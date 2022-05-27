<?php
namespace Topsort\Integration\Block\Tracking\Impressions;

use Magento\Framework\Exception\LocalizedException;

/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
class SearchPage extends ProductListPageAbstract
{
    /**
     * @return string
     */
    protected function getProductListBlock()
    {
        return 'search_result_list';
    }
}