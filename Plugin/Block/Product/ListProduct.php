<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
declare(strict_types=1);

namespace Topsort\Integration\Plugin\Block\Product;

class ListProduct
{
    function aroundToHtml(\Magento\Catalog\Block\Product\ListProduct $block, callable $proceed)
    {
        return $proceed();
    }
}