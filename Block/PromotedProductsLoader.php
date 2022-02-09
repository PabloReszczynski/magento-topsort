<?php
namespace Topsort\Integration\Block;
use Magento\Framework\View\Element\Template;

/**
 * Topsort Magento Extension
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
class PromotedProductsLoader extends \Magento\Framework\View\Element\Template
{
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

    public function getProductsContainerSelector()
    {
        return 'ol.products';
    }

    public function getLoaderMaskContainerSelector()
    {
        return 'div.products';
    }

}