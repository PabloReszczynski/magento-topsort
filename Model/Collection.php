<?php
/**
 * BIM Modules.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @copyright Copyright (c) Kyrylo Kostiukov 2018 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license Commercial BIM Software License 1.1 (BIM 1.1) https://www.bimproject.net/bim-license-v1.1.txt
 * @project magento-bim
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