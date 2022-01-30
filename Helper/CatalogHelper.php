<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Helper;

use Magento\Framework\App\Helper\Context;

class CatalogHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollectionFactory;
    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    private $stockState;

    function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        Context $context
    )
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->stockState = $stockState;
        parent::__construct($context);
    }

    function getCategoryFullName(\Magento\Catalog\Model\Category $category)
    {
        /** @var \Magento\Catalog\Model\Category $parent */
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $parents */
        $parents = $this->categoryCollectionFactory->create();
        $parentIds = $category->getParentIds();
        $parents->addFieldToFilter('entity_id', array('in' => $parentIds));
        $parents->addAttributeToSelect('name');
        $path = '';
        $parentNames = [];
        foreach ($parents as $parent) {
            $parentNames[$parent->getId()] = $parent->getName();
        }
        foreach ($parentIds as $parentId) {
            $path .= $parentNames[$parentId] . '/';
        }
        $path .= $category->getName();
        return $path;
    }

    /**
     * Retrieve stock qty whether product
     *
     * @param int $productId
     * @param int $websiteId
     * @return float
     */
    public function getStockQty($productId, $websiteId = null)
    {
        return $this->stockState->getStockQty($productId, $websiteId);
    }
}
