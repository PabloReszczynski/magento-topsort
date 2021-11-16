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
    /**
     * @var \Topsort\Integration\Helper\CatalogHelper
     */
    private $catalogHelper;
    /**
     * @var \Topsort\Integration\Helper\Data
     */
    private $dataHelper;

    function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Topsort\Integration\Helper\Data $dataHelper,
        \Topsort\Integration\Helper\CatalogHelper $catalogHelper,
        Context $context
    )
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->catalogHelper = $catalogHelper;
        $this->dataHelper = $dataHelper;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        // validate Bearer header
        if (!$this->dataHelper->validateApiAuthorization($result, $this->getRequest(), $this->getResponse())) {
            return $result;
        }

        // "prev" Page token
        $prev = intval($this->getRequest()->getParam('prev', 0));

        // "next" Page token
        $next = intval($this->getRequest()->getParam('next', 0));

        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->categoryCollectionFactory->create();
        $collection->setOrder('name');
        $collection->addAttributeToSelect('name');
        $pageSize = $this->dataHelper->getCatalogRequestPageSize();
        $collection->setPageSize($pageSize);
        $lastPage = $collection->getLastPageNumber();

        $pageNum = $prev > 0 ? $prev : ($next > 0 ? $next : 1);

        if ($pageNum > $lastPage) {
            $result->setHttpResponseCode(404);
            $result->setData([
                'prev' => null,
                'next' => $lastPage,
                'response' => []
            ]);
            return $result;
        }
        $collection->setPage(intval($pageNum), $pageSize);

        $categories = [];
        foreach ($collection as $item) {
            /** @var \Magento\Catalog\Model\Category $item */
            $categories[] = [
                'id' => strval($item->getId()),
                'name' => $this->catalogHelper->getCategoryFullName($item)
            ];
        }

        if (empty($categories)) {
            $result->setHttpResponseCode(404);
        }

        $result->setData([
            'prev' => $pageNum > 1 ? $pageNum - 1 : null,
            'next' => $pageNum >= $lastPage ? null : $pageNum + 1,
            'response' => $categories
        ]);

        return $result;
    }
}