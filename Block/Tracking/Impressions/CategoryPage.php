<?php
namespace Topsort\Integration\Block\Tracking\Impressions;

use Magento\Framework\Exception\LocalizedException;

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
class CategoryPage extends ProductListPageAbstract
{
    /**
     * @return string
     */
    protected function getProductListBlock()
    {
        return 'category.products.list';
    }
}