<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Model\ResourceModel\Banner\Grid;

use Magento\Framework\Search\AggregationInterface;
use Topsort\Integration\Model\ResourceModel\Banner\Grid\Collection\Item;

/**
 * Collection for displaying grid of banners
 */
class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return \Magento\Framework\Data\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (empty($this->_items)) {
            $this->_items = [
                new Item(['id' => base64_encode('Home-page|400x500'), 'width' => '400', 'height' => '500', 'placement' => 'Home-page']),
                new Item(['id' => base64_encode('Category-page|600x200'), 'width' => '600', 'height' => '200', 'placement' => 'Category-page']),
            ];
        }
        return $this;
    }

    function getBannerDataById($bannerId)
    {
        $data = explode('|', base64_decode($bannerId));
        $placement = $data[0] ?? 'unknown';
        $dimensions = $data[1] ?? 'unknown';
        $dimensionsArray = explode('x', $dimensions);
        return [
            'id' => $bannerId,
            'placement' => $placement,
            'width' => intval($dimensionsArray[0] ?? 0),
            'height' => intval($dimensionsArray[1] ?? 0)
        ];
    }

    function getBannerIdForData($placement, $width, $height)
    {
        return base64_encode($placement . '|' . $width . 'x' . $height);
    }
}
