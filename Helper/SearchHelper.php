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
    /**
     * @var Data
     */
    private $dataHelper;

    function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Topsort\Integration\Helper\Data $dataHelper,
        Context $context
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->dataHelper = $dataHelper;
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

        $collection->addAttributeToSelect(array_unique([
            $this->dataHelper->getTopsortBrandsAttributeCode(),
            $this->dataHelper->getTopsortVendorAttributeCode(),
            'name',
            'price',
            'image',
            'description',
            'short_description'
        ]));

        if (!empty($categoryId)) {
            $categoryId = $this->categoryFactory->create()->load($categoryId);
            $collection->addCategoryFilter($categoryId);
        }

        return $collection;
    }
}
