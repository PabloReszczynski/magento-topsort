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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;


    function __construct(
        CollectionHelper $collectionHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        Api $topsortApi,
        \Magento\Framework\App\Action\Context $actionContext,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->collectionHelper = $collectionHelper;
        $this->topsortApi = $topsortApi;
        $this->productRepository = $productRepository;
        $this->actionContext = $actionContext;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    public function setupPromotedProductsInCollection($collection)
    {
        if (!$collection->hasFlag('topsort_promotions_load_mode')
            || $collection->getFlag('topsort_promotions_load_mode') !== true) {
            return;
        }

        $promotedProductsCount = $collection->getFlag('topsort_promotions_count');
        $productsLimit = $collection->getFlag('topsort_products_limit');
        $preloadBannerData = $collection->getFlag('preload_banner_data');

        $action = $this->actionContext->getRequest()->getFullActionName();

        $initialItems = $collection->getItems();

        // track impressions
        $impressions = [];

        try {
            $curPage = $collection->getCurPage();
            $pageSize = $collection->getPageSize();
            if ($curPage && $curPage > 1) {
                // display results only on the first page if paging is used

                // clean up collection since only promoted products should be in the collection
                foreach ($collection as $key => $item) {
                    $collection->removeItemByKey($key);
                }
                // run getSponsoredProducts call to load banners if needed
                if ($preloadBannerData) {
                    $allSku = $this->collectionHelper->getAllSku($collection);
                    $this->topsortApi->getSponsoredProducts($allSku, $promotedProductsCount, $preloadBannerData);
                }
                return;
            }

            // check the products limit
            $productsCount = $collection->count();
            if ($productsLimit > 0 && $pageSize > $productsLimit && $productsCount < $productsLimit) {
                // productsLimit should be less then the page size
                // if productsLimit is not reached - no sponsored products should be shown

                // clean up collection since only promoted products should be in the collection
                foreach ($collection as $key => $item) {
                    $collection->removeItemByKey($key);
                }

                // run getSponsoredProducts call to load banners if needed
                if ($preloadBannerData) {
                    $allSku = $this->collectionHelper->getAllSku($collection);
                    $this->topsortApi->getSponsoredProducts($allSku, $promotedProductsCount, $preloadBannerData);
                }
                return;
            }
            $allSku = $this->collectionHelper->getAllSku($collection);
            $result = $this->topsortApi->getSponsoredProducts($allSku, $promotedProductsCount, $preloadBannerData);
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

            // replace items in the collection
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
                    'auction_id' => $auctionId
                ];
            }

            $this->topsortApi->trackImpressions($action, $impressions);

        } catch (\Exception $e) {
            // something did not work, we write the exception to the log and return the collection to its initial state
            // TODO if loading of promotions failed, no products should be in the collection - it should be empty
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
