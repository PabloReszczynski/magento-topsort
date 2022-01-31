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
    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    private $cacheState;

    function __construct(
        ResultFactory $resultFactory,
        \Magento\Framework\App\Cache\StateInterface $cacheState
    )
    {
        $this->resultFactory = $resultFactory;
        $this->cacheState = $cacheState;
    }

    function aroundExecute(\Magento\Catalog\Controller\Category\View $actionModel, callable $proceed)
    {
        /** @var \Magento\Framework\View\Result\Page $page */
        $page = $proceed();
        if ($actionModel->getRequest()->getParam('load-promotions')) {
            $block = $page->getLayout()->getBlock('category.products.list');

            /** @var \Magento\Framework\Controller\Result\Json $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            //echo $block->toHtml();exit;
            $result->setData(['html' => $block->toHtml()]);
            // disable browser cache
            $result->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);

            // disable FPC
            $this->cacheState->setEnabled(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER, false);

            return $result;
        }
        return $page;
    }
}