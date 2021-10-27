<?php

namespace Topsort\Integration\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ObserverInterface;
use Topsort\Integration\Model\Api;
use Topsort\Integration\Model\Product\CollectionHelper;

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

    function __construct(
        CollectionHelper $collectionHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        Api $topsortApi,
        \Magento\Framework\App\Action\Context $actionContext,
        \Topsort\Integration\Helper\Data $helperData,
        \Psr\Log\LoggerInterface $logger
    )
    {

        $this->collectionHelper = $collectionHelper;
        $this->topsortApi = $topsortApi;
        $this->productRepository = $productRepository;
        $this->actionContext = $actionContext;
        $this->helperData = $helperData;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $observer->getData('collection');

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

            $sponsoredItemSkuList = $this->topsortApi->getSponsoredProducts($allSku, $promotedProductsCount);
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
        } catch (\Exception $e) {
            // something did not work, we write the exception to the log and return the collection to its initial state
            $this->logger->critical($e);
            $collection->clear();
        }
    }
}