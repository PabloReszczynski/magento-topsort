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
namespace Topsort\Integration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Topsort\Integration\Model\Api;
use Topsort\Integration\Model\RefererUrl;

class ProductViewAction implements ObserverInterface
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
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    )
    {
        $this->topsortApi = $topsortApi;
        $this->refererUrl = $refererUrl;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $observer->getData('request');

        $refererRouteData = $this->refererUrl->getRefererRouteData();

        $productId = $request->getParam('id');

        try {
            $product = $this->productRepository->getById($productId);
        } catch (\Exception $e) {
            // product was not found: log error and do nothing
            $this->logger->critical($e);
            return;
        }
        $auctionId = $request->getParam('auction_id', null);

        $this->topsortApi->trackProductClick(
            $refererRouteData['url_path'],
            'position_0',
            $product->getSku(),
            $auctionId
        );
    }
}