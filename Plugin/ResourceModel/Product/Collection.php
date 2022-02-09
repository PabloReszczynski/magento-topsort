<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Plugin\ResourceModel\Product;

use Topsort\Integration\Helper\ProductCollectionHelper;

class Collection
{
    /**
     * @var ProductCollectionHelper
     */
    private $collectionHelper;

    function __construct(
        ProductCollectionHelper $collectionHelper
    )
    {
        $this->collectionHelper = $collectionHelper;
    }

    function beforeLoad(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection, $printQuery = false, $logQuery = false)
    {
        if ($collection->hasFlag('topsort_promotions_load_mode')
        && !$collection->getFlag('promotions_initialization')) {
            // avoid recursion
            $collection->setFlag('promotions_initialization', true);

            $this->collectionHelper->setupPromotedProductsInCollection($collection);

            // allow to reload multiple times (and re-initialize promotions)
            $collection->setFlag('promotions_initialization', false);
        }
        return null; // return null here means that we do not change the original arguments
    }

}