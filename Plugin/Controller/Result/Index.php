<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
declare(strict_types=1);

namespace Topsort\Integration\Plugin\Controller\Result;

use Magento\Framework\Controller\ResultFactory;

class Index
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;
    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    private $cacheState;
    /**
     * @var \Topsort\Integration\Controller\Search\Result\Index
     */
    private $customAction;

    function __construct(
        ResultFactory $resultFactory,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Topsort\Integration\Controller\Search\Result\Index $customAction
    )
    {
        $this->resultFactory = $resultFactory;
        $this->cacheState = $cacheState;
        $this->customAction = $customAction;
    }

    function aroundExecute(\Magento\CatalogSearch\Controller\Result\Index $actionModel, callable $proceed)
    {
        if ($actionModel->getRequest()->getParam('load-promotions')) {
            $view = $this->customAction->executeLoadPromotionsAction($actionModel, 'catalogsearch_result_index_topsort_promotions');

            $block = $view->getLayout()->getBlock('search_result_list');

            /** @var \Magento\Framework\Controller\Result\Json $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

            $html = $block->toHtml();
//            $html = '<div>' . $html . '</div>';
//            $doc = new \DomDocument();
//            @$doc->loadHTML($html);
//            $finder = new \DOMXPath($doc);
//            $productItemNodes = $finder->query("//li");
//            //var_export($productItemNodes->count());exit;
//            $productsHtml = '';
//            foreach ($productItemNodes as $productItemNode) {
//                /** @var \DOMNode $productItemNode */
//                $productsHtml .= $productItemNode->ownerDocument->saveHTML($productItemNode);
//            }
//echo $productsHtml;exit;
            $productsHtml = $html;
            $result->setData(['html' => $productsHtml]);
            // disable browser cache
            // Note: consider using $this->getResponse()->setNoCacheHeaders();
            $result->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);

            // disable FPC
            $this->cacheState->setEnabled(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER, false);

            return $result;
        }
        return $proceed();
    }
}