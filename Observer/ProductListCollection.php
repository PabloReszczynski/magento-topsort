<?php
/**
 * TopSort Magento Extension
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @copyright Copyright (c) TopSort 2021 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license Proprietary
 */
namespace Topsort\Integration\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ObserverInterface;
use Topsort\Integration\Model\Api;
use Topsort\Integration\Model\Product\CollectionHelper;
use Magento\UrlRewrite\Model\UrlFinderInterface;

class ProductListCollection implements ObserverInterface
{
    /**
     * @var CollectionHelper
     */
    private $collectionHelper;
    /**
     * @var Api
     */
    private $topsortApi;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $actionContext;
    /**
     * @var \Topsort\Integration\Helper\Data
     */
    private $helperData;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;
    /**
     * @var \Magento\Framework\App\Request\PathInfo
     */
    private $pathInfoService;

    function __construct(
        CollectionHelper $collectionHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        Api $topsortApi,
        \Magento\Framework\App\Action\Context $actionContext,
        \Topsort\Integration\Helper\Data $helperData,
        \Psr\Log\LoggerInterface $logger,
        UrlFinderInterface $urlFinder,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Request\PathInfo $pathInfoService
    )
    {

        $this->collectionHelper = $collectionHelper;
        $this->topsortApi = $topsortApi;
        $this->productRepository = $productRepository;
        $this->actionContext = $actionContext;
        $this->helperData = $helperData;
        $this->logger = $logger;
        $this->urlFinder = $urlFinder;
        $this->redirect = $redirect;
        $this->url = $url;
        $this->pathInfoService = $pathInfoService;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $observer->getData('collection');
        $initialItems = $collection->getItems();
        try {
            $action = $this->actionContext->getRequest()->getFullActionName();
            $promotedProductsCount = 0;
            $productsLimit = 0;

            $h = $this->helperData;
            if ($action == 'catalog_category_view' && $h->getIsEnabledOnCatalogPages()) {
                $promotedProductsCount = $h->getPromotedProductsAmountForCatalogPages();
                $productsLimit = $h->getMinProductsAmountForCatalogPages();
            } else if ($action == 'catalogsearch_result_index' && $h->getIsEnabledOnSearch()) {
                $promotedProductsCount = $h->getPromotedProductsAmountForSearch();
                $productsLimit = $h->getMinProductsAmountForSearch();
            } else {
                // do nothing
                return;
            }

            $curPage = $collection->getCurPage();
            $pageSize = $collection->getPageSize();
            if ($curPage && $curPage > 1) {
                // display results only for on the first page if paging is used
                return;
            }

            // check the products limit
            $productsCount = $collection->count();
            if ($productsLimit > 0 && $pageSize > $productsLimit && $productsCount < $productsLimit) {
                // productsLimit should be less then the page size
                // if productsLimit is not reached - no sponsored products should be shown
                return;
            }

            $allSku = $this->collectionHelper->getAllSku($collection);
            // $allSku = ['4RfbhmIx']; // demo SKUs to test

            $result = $this->topsortApi->getSponsoredProducts($allSku, $promotedProductsCount);
            $sponsoredItemSkuList = isset($result['products']) ? $result['products'] : [];
            $auctionId = isset($result['auction_id']) ? $result['auction_id'] : null;
            $sponsoredItemsList = [];

            foreach ($sponsoredItemSkuList as $sponsoredItemSku) {
                /** @var Product $product */
                try {
                    $product = $this->productRepository->get($sponsoredItemSku);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    // if the product was not found do nothing
                    continue;
                }
                $sponsoredItemsList[] = $product;
            }

            //$count = 0;
            if ($sponsoredItemSkuList) {
                // insert items at the beginning of the collection
                $items = $collection->getItems();
                foreach ($collection as $key => $item) {
                    $collection->removeItemByKey($key);
                }

                // add new items
                foreach ($sponsoredItemsList as $item) {
                    //$count++;
                    $id = $item->getId();
                    $item->setIsPromoted(true);
                    $item->setAuctionId($auctionId);
                    $item->setId($id . '-promoted');
                    $collection->addItem($item);
                    $item->setId($id);
                }

                // re-add $items
                foreach ($items as $item) {
                    //$count++;
                    $collection->addItem($item);
                }
            }

            // track impressions
            $impressions = [];
            foreach ($collection as $item) {
                /** @var Product $item */
                $impression = [
                    'product_id' => $item->getId(),
                    'sku' => $item->getSku()
                ];
                if ($item->getIsPromoted() === true) {
                    $impression['auctionId'] = $item->getAuctionId();
                }
                $impressions[] = $impression;
            }

            $this->topsortApi->trackImpressions($action, $impressions);
        } catch (\Exception $e) {
            // something did not work, we write the exception to the log and return the collection to its initial state
            $this->logger->critical($e);

            foreach ($collection as $key => $item) {
                $collection->removeItemByKey($key);
            }
            foreach ($initialItems as $item) {
                $collection->addItem($item);
            }

            // Do not use method $collection->clear() here. It has a bug: clear() does not reset the _isFiltersRendered flag.
        }
    }
}