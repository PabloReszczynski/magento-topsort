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
namespace Topsort\Integration\Controller\Api;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Categories extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollectionFactory;

    function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        Context $context
    )
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        // validate Bearer header
        $authHeader = $this->getRequest()->getHeader('Authorization');
        if (!$authHeader) {
            echo 'No Authorization header';exit;
        }
        // TODO move the token into config
        $validToken = 'dfajgnpahdgprgjnfdkj4054375nmcnorythqe';
        $authHeaderParts = explode(' ', $authHeader);
        if (count($authHeaderParts) !== 2 || $authHeaderParts[0] != 'Bearer') {
            echo 'Invalid Authorization header';exit;
        }
        $token = $authHeaderParts[1];
        if ($token != $validToken) {
            echo 'Invalid token';exit;
        }

        // The last category ID on the previous page, relative to the requested page.
        // When provided, will get the categories (ordered alphabetically) which come after this category.
        $prev = $this->getRequest()->getParam('prev');

        // The first category ID on the next page, relative to the requested page.
        // When provided, will get the categories (ordered alphabetically) which come before this category.
        $next = $this->getRequest()->getParam('next');

        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->categoryCollectionFactory->create();
        $collection->setOrder('entity_id');
        $collection->addAttributeToSelect('name');
        if ($prev !== null) {
            $collection->addFieldToFilter('entity_id', ['gt' => $prev]);
        }
        if ($next !== null) {
            $collection->addFieldToFilter('entity_id', ['lt' => $next]);
        }
        $categories = [];
        foreach ($collection as $item) {
            /** @var \Magento\Catalog\Model\Category $item */
            /** @var \Magento\Catalog\Model\Category $parent */
            /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $parents */
            $parents = $this->categoryCollectionFactory->create();
            $parentIds = $item->getParentIds();
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
            $path .= $item->getName();

            $categories[] = [
                'id' => strval($item->getId()),
                'name' => $path
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData($categories);
        return $result;
    }
}