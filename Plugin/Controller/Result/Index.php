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

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Controller\ResultFactory;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;

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
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var QueryFactory
     */
    private $queryFactory;
    /**
     * @var Resolver
     */
    private $layerResolver;

    function __construct(
        ResultFactory $resultFactory,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Topsort\Integration\Controller\Search\Result\Index $customAction,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver
    )
    {
        $this->resultFactory = $resultFactory;
        $this->cacheState = $cacheState;
        $this->customAction = $customAction;
        $this->storeManager = $storeManager;
        $this->queryFactory = $queryFactory;
        $this->layerResolver = $layerResolver;
    }

    function aroundExecute(\Magento\CatalogSearch\Controller\Result\Index $actionModel, callable $proceed)
    {
        if ($actionModel->getRequest()->getParam('load-promotions')) {

            // initialize query
            $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);

//            /* @var $query \Magento\Search\Model\Query */
//            $query = $this->queryFactory->get();
//
//            $storeId = $this->storeManager->getStore()->getId();
//            $query->setStoreId($storeId);
//
//            $queryText = $query->getQueryText();

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