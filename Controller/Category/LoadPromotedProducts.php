<?php
/**
 * Topsort Magento Extension
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Controller\Category;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Topsort\Integration\Model\Api;
use Topsort\Integration\Model\RefererUrl;

class LoadPromotedProducts extends \Magento\Framework\App\Action\Action
{

    /**
     * @var Api
     */
    private $topsortApi;
    /**
     * @var RefererUrl
     */
    private $refererUrl;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    function __construct(
        Api $topsortApi,
        RefererUrl $refererUrl,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        Context $context
    )
    {
        $this->topsortApi = $topsortApi;
        $this->refererUrl = $refererUrl;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $categoryId = $this->getRequest()->getParam('id');

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $responseData = ['html' => 'productHTML 11111111111111'];
        $result->setData($responseData);
        return $result;

        $referrerUrl = $this->getRequest()->getParam('referrer');
        $refererRouteData = $this->refererUrl->getRefererRouteData($referrerUrl);

        $request = $this->getRequest();
        $productId = $request->getParam('id');

        try {
            $product = $this->productRepository->getById($productId);
        } catch (\Exception $e) {
            // product was not found: log error and do nothing
            $this->logger->critical($e);
            return;
        }
        $auctionId = $request->getParam('auction_id', null);

        $apiCallResult = $this->topsortApi->trackProductClick(
            empty($refererRouteData['url_path']) ? 'unknown' : $refererRouteData['url_path'],
            1,
            $product->getSku(),
            $auctionId
        );

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData($apiCallResult);
        return $result;
    }
}