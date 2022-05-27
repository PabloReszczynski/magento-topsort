<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Bim\Etl\Model;

class Collection extends \Magento\Framework\Data\Collection
{
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
    ) {
        parent::__construct(
            $entityFactory
        );
    }

    public function addFieldToFilter($field, $condition = null)
    {
        throw new \Magento\Framework\Exception\LocalizedException(__('This collection does not support filtering'));
    }
}