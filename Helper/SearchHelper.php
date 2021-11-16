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
namespace Topsort\Integration\Helper;

use Magento\Framework\App\Helper\Context;

class SearchHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    private $categoryFactory;

    function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        Context $context
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->categoryFactory = $categoryFactory;
        parent::__construct($context);
    }

    /**
     * @param $query
     * @param $categoryId
     * @param $pageNum
     * @param $pageSize
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    function searchProducts($query, $categoryId, $pageNum, $pageSize = 51)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();

        $collection->setPageSize($pageSize);
        $collection->setCurPage($pageNum);
        $collection->addOrder('id', 'asc');

        $collection->addFieldToFilter('visibility', ['neq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE]);

        if (!empty($query)) {
            $collection->addAttributeToFilter([
                'name' => [
                    'attribute' => 'name',
                    'like' => '%' . $query . '%'
                ],
                'sku' => [
                    'attribute' => 'sku',
                    'like' => '%' . $query . '%'
                ]
            ]);
        }
        $collection->addAttributeToSelect('*');

        if (!empty($categoryId)) {
            $categoryId = $this->categoryFactory->create()->load($categoryId);
            $collection->addCategoryFilter($categoryId);
        }

        return $collection;
    }
}