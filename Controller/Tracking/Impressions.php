<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Controller\Tracking;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Topsort\Integration\Model\Api;
use Topsort\Integration\Model\RefererUrl;

class Impressions extends \Magento\Framework\App\Action\Action
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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    function __construct(
        Api $topsortApi,
        RefererUrl $refererUrl,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        Context $context
    )
    {
        $this->topsortApi = $topsortApi;
        $this->refererUrl = $refererUrl;
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $request = $this->getRequest();
        $productIds = explode(',', $request->getParam('ids'));
        $page = $request->getParam('page');

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('entity_id', ['in' => $productIds]);

        $impressions = [];
        foreach ($collection as $product) {
            $impressions[$product->getSku()] = [
                'product_id' => $product->getId(),
                'sku' => $product->getSku()
            ];
        }

        $apiCallResult = $this->topsortApi->trackImpressions(
            $page,
            $impressions
        );

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData($apiCallResult);
        return $result;
    }
}
