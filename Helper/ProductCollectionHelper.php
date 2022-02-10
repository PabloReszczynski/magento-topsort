<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ObserverInterface;
use Topsort\Integration\Model\Api;
use Topsort\Integration\Model\Product\CollectionHelper;
use Magento\UrlRewrite\Model\UrlFinderInterface;

class ProductCollectionHelper
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
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    public function setupPromotedProductsInCollection($collection)
    {
        if (!$collection->hasFlag('topsort_promotions_load_mode')
            || $collection->getFlag('topsort_promotions_load_mode') === null) {
            return;
        }

        $collectionLoadMode = $collection->getFlag('topsort_promotions_load_mode');
        $promotedProductsCount = $collection->getFlag('topsort_promotions_count');
        $productsLimit = $collection->getFlag('topsort_products_limit');
        $onlyPromotedProducts = $collectionLoadMode === 'load_only_topsort_promotions';

        $action = $this->actionContext->getRequest()->getFullActionName();

        $initialItems = $collection->getItems();
        // track impressions
        $impressions = [];
        foreach ($initialItems as $item) {
            /** @var Product $item */
            $impressions[$item->getSku()] = [
                'product_id' => $item->getId(),
                'sku' => $item->getSku()
            ];
        }

        try {
            $curPage = $collection->getCurPage();
            $pageSize = $collection->getPageSize();
            if ($curPage && $curPage > 1) {
                // display results only on the first page if paging is used

                // clean up collection if only promoted products should be in the collection
                if ($onlyPromotedProducts) {
                    foreach ($collection as $key => $item) {
                        $collection->removeItemByKey($key);
                    }
                }
                $this->topsortApi->trackImpressions($action, $impressions);
                return;
            }

            // check the products limit
            $productsCount = $collection->count();
            if ($productsLimit > 0 && $pageSize > $productsLimit && $productsCount < $productsLimit) {
                // productsLimit should be less then the page size
                // if productsLimit is not reached - no sponsored products should be shown

                // clean up collection if only promoted products should be in the collection
                if ($onlyPromotedProducts) {
                    foreach ($collection as $key => $item) {
                        $collection->removeItemByKey($key);
                    }
                }
                $this->topsortApi->trackImpressions($action, $impressions);
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

            if (count($sponsoredItemSkuList) > 0 || $onlyPromotedProducts) {
                // insert items at the beginning of the collection
                $items = $collection->getItems();
                foreach ($collection as $key => $item) {
                    $collection->removeItemByKey($key);
                }
                // add new items
                foreach ($sponsoredItemsList as $item) {
                    /** @var Product $item */
                    $id = $item->getId();
                    $item->setIsPromoted(true);
                    $item->setAuctionId($auctionId);
                    $item->setId($id . '-promoted');
                    $collection->addItem($item);
                    $item->setId($id);

                    // add impressions to track
                    $impressions[$item->getSku()] = [
                        'product_id' => $id,
                        'sku' => $item->getSku(),
                        'auctionId' => $auctionId
                    ];
                }

                // re-add $items
                if (!$onlyPromotedProducts) {
                    foreach ($items as $item) {
                        // fix for situations where promoted products were already loaded before into the collection
                        if ($item->getIsPromoted()) {
                            continue;
                        }
                        $collection->addItem($item);
                    }
                }
            }

            $this->topsortApi->trackImpressions($action, $impressions);

        } catch (\Exception $e) {
            // something did not work, we write the exception to the log and return the collection to its initial state
            $this->logger->critical($e);

            foreach ($collection as $key => $item) {
                $collection->removeItemByKey($key);
            }

            foreach ($initialItems as $item) {
                // fix for situations where promoted products were already loaded before into the collection
                if ($item->getIsPromoted()) {
                    continue;
                }
                $collection->addItem($item);
            }

            // Do not use method $collection->clear() here. It has a bug: clear() does not reset the _isFiltersRendered flag.
        }
    }
}
