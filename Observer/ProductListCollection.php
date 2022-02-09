<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
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
        $collectionLoadMode = $promotedProductsCount = $productsLimit = null;
        if (($action == 'catalog_category_view' || $action == 'catalogsearch_result_index')
            && $h->getIsEnabledOnCatalogPages()
            && $this->actionContext->getRequest()->getParam('load-promotions', false)) {
            // only add products during the ajax request
            $collectionLoadMode = 'load_only_topsort_promotions'; // Note: alternative mode is add_topsort_promotions, but its not used anymore
            $promotedProductsCount = $h->getPromotedProductsAmountForCatalogPages();
            $productsLimit = $h->getMinProductsAmountForCatalogPages();
        } //else {
            // do nothing, $collectionLoadMode is not assigned
        //}

        // mark collection to apply the logic of promoted products once it will be loaded
        if ($collectionLoadMode !== null) {
            $collection->setFlag('topsort_promotions_load_mode', $collectionLoadMode);
            $collection->setFlag('topsort_promotions_count', $promotedProductsCount);
            $collection->setFlag('topsort_products_limit', $productsLimit);
        }
    }
}
