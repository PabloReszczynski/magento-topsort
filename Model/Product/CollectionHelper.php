<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Model\Product;

class CollectionHelper extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    function getAllSku(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection)
    {
        $idsSelect = $this->getAllSkuSelect($collection);

        return $this->getConnection()->fetchCol($idsSelect, $collection->_bindParams);
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param \Magento\Framework\DB\Select $select
     * @param array $bindParams
     * @return array
     */
    function getAllSkuUsingSelectAndBindParams($collection, $select, $bindParams)
    {
        return $collection->getConnection()->fetchCol($select, $bindParams);
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Framework\DB\Select
     */
    public function getAllSkuSelect(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection)
    {
        $idsSelect = $collection->_getClearSelect();
        $idsSelect->columns('e.sku');
        //$idsSelect->limit(null, null); // TODO here the hard limit could be specified (e.g. 50.000 products)
        $idsSelect->resetJoinLeft();
        $idsSelect->reset(\Magento\Framework\DB\Select::RIGHT_JOIN);
        $idsSelect->reset(\Magento\Framework\DB\Select::INNER_JOIN);
        return $idsSelect;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return array
     */
    public function getBindParamsForAllSkuSelect(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection)
    {
        return $collection->_bindParams;
    }
}
