<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Plugin\ResourceModel\Product;

use Topsort\Integration\Model\Api;
use Topsort\Integration\Model\Product\CollectionHelper;

class Collection
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
     * @var \Magento\Framework\App\Action\Context
     */
    private $actionContext;

    function __construct(
        \Topsort\Integration\Model\Product\CollectionHelper $collectionHelper,
        Api $topsortApi,
        \Magento\Framework\App\Action\Context $actionContext
    )
    {
        $this->collectionHelper = $collectionHelper;
        $this->topsortApi = $topsortApi;
        $this->actionContext = $actionContext;
    }

    function beforeLoad(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection, $printQuery = false, $logQuery = false)
    {
        if ($collection->isLoaded()) {
            return null;
        }
        if ($collection->hasFlag('topsort_promotions_load_mode')
        && !$collection->getFlag('promotions_initialization')) {

            // avoid recursion
            $collection->setFlag('promotions_initialization', true);

            $this->initPromotionsLoading($collection);

            // allow to reload multiple times (and re-initialize promotions)
            $collection->setFlag('promotions_initialization', false);
        }
        return null; // return null here means that we do not change the original arguments
    }

    function afterLoad(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection, $result, $printQuery = false, $logQuery = false)
    {
        if ($collection->hasFlag('auction_id') && $collection->getFlag('auction_id')) {
            $auctionId = $collection->getFlag('auction_id');
            // reset the flag in order to avoid recursion
            $collection->setFlag('auction_id', null);

            // track impressions of products with promotions (they are loaded via ajax)
            $impressions = [];
            foreach ($collection as $item) {
                /** @var $item \Magento\Catalog\Model\Product */
                $item->setAuctionId($auctionId);
                $item->setIsPromoted(true);

                $impressions[$item->getSku()] = [
                    'product_id' => $item->getId(),
                    'sku' => $item->getSku(),
                    'auction_id' => $auctionId
                ];
            }
            if (!empty($impressions)) {
                $action = $this->actionContext->getRequest()->getFullActionName();
                $this->topsortApi->trackImpressions($action, $impressions);
            }
        }
        return $result;
    }

    protected function initPromotionsLoading(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection)
    {
        $curPage = $this->collectionHelper->getCurPageWithoutLoad($collection);
        $promotedProductsCount = $collection->getFlag('topsort_promotions_count');
        $preloadBannerData = $collection->getFlag('preload_banner_data');
        if ($curPage > 1) {
            $promotedProductsCount = 0;
            $collection->setFlag('topsort_promotions_count', $promotedProductsCount);
        }
        if (!$preloadBannerData && $promotedProductsCount == 0) {
            // no need to load anything
            $collection->setFlag('skip_promotions_request', true);
        }
        //disable paging
        $collection->setCurPage(1);
        $collection->setPageSize(0);
    }
}