<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductListCollection implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $actionContext;
    /**
     * @var \Topsort\Integration\Helper\Data
     */
    private $helperData;

    function __construct(
        \Magento\Framework\App\Action\Context $actionContext,
        \Topsort\Integration\Helper\Data $helperData
    )
    {

        $this->actionContext = $actionContext;
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $observer->getData('collection');
        $action = $this->actionContext->getRequest()->getFullActionName();
        $h = $this->helperData;
        $promotionsLoadMode = $promotedProductsCount = $productsLimit = $preloadBannerData =null;
        if (($action == 'catalog_category_view' || $action == 'catalogsearch_result_index')
            && $h->getIsEnabledOnCatalogPages()
            && $this->actionContext->getRequest()->getParam('load-promotions', false)) {
            // only add products during the ajax request
            $promotionsLoadMode = true;
            $promotedProductsCount = $h->getPromotedProductsAmountForCatalogPages();
            $productsLimit = $h->getMinProductsAmountForCatalogPages();
            $preloadBannerData = $this->isBannerDataNeeded();
        } //else {
            // do nothing, $collectionLoadMode is not assigned
        //}

        // mark collection to apply the logic of promoted products once it will be loaded
        if ($promotionsLoadMode) {
            $collection->setFlag('topsort_promotions_load_mode', true);
            $collection->setFlag('topsort_promotions_count', $promotedProductsCount);
            $collection->setFlag('topsort_products_limit', $productsLimit);
            $collection->setFlag('preload_banner_data', $preloadBannerData);
        }
    }

    private function isBannerDataNeeded()
    {
        return $this->actionContext->getRequest()->getParam('banners') ? true : false;
    }
}
