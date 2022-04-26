<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Model\ResourceModel\Banner\Grid\Collection;

use Magento\Framework\DataObject;

/**
 * Collection for displaying grid of banners
 */
class Item extends DataObject
{

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        $this->setData('id', $id);
    }
}