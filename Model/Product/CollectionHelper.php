<?php
/**
 * TopSort Magento Extension
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @copyright Copyright (c) TopSort 2021 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license Proprietary
 */
namespace Topsort\Integration\Model\Product;

class CollectionHelper extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    function getAllSku(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection)
    {
        $idsSelect = $collection->_getClearSelect();
        $idsSelect->columns('e.sku');
        //$idsSelect->limit(null, null); // TODO here the hard limit could be specified (e.g. 50.000 products)
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchCol($idsSelect, $collection->_bindParams);
    }
}