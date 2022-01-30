<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
declare(strict_types=1);

namespace Topsort\Integration\Plugin\Controller\Category;

use Magento\Framework\Controller\ResultFactory;

class View
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    function __construct(
        ResultFactory $resultFactory
    )
    {
        $this->resultFactory = $resultFactory;
    }

    function aroundExecute(\Magento\Catalog\Controller\Category\View $actionModel, callable $proceed)
    {
        /** @var \Magento\Framework\View\Result\Page $page */
        $page = $proceed();
        if ($actionModel->getRequest()->getParam('load-promotions')) {
            $block = $page->getLayout()->getBlock('category.products.list'); // category.products

            /** @var \Magento\Framework\Controller\Result\Json $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $result->setData(['html' => $block->toHtml()]);
            return $result;
        }
        return $page;
    }
}