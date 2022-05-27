<?php
namespace Topsort\Integration\Block;

use Magento\Framework\View\Element\Template;

/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
class PromotedProductsLoader extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Topsort\Integration\Helper\Data
     */
    private $dataHelper;

    function __construct(
        \Topsort\Integration\Helper\Data $dataHelper,
        Template\Context $context,
        array $data = [])
    {
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }


    function getLoaderIconUrl()
    {
        return $this->getViewFileUrl('images/loader-2.gif');
    }

    function getLoadPromotionsUrl()
    {
        return $this->getUrl('*/*/*', [
            '_current' => true,
            '_use_rewrite' => true,
            '_query' => ['load-promotions' => 1]
        ]);
    }

    public function getToolbarSelector()
    {
        return 'div.toolbar-products';
    }

    public function getProductsLimit()
    {
        return intval($this->dataHelper->getMinProductsAmountForCatalogPages());
    }

    public function getProductsContainerSelector()
    {
        return 'ol.products';
    }

    public function getLoaderMaskContainerSelector()
    {
        return 'div.products';
    }

}