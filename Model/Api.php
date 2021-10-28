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
namespace Topsort\Integration\Model;

class Api
{
    /**
     * @var \Topsort\Integration\Helper\Data
     */
    private $helper;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    function __construct(
        \Topsort\Integration\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
    }


    function getSponsoredProducts($productSkuValues, $promotedProductsCount)
    {
        if (!$this->helper->getIsEnabled()) {
            return [];
        }
        $apiKey = $this->helper->getApiKey();
        $apiUrl = $this->helper->getApiUrl();
        $sdk = new \Topsort\SDK('magento-marketplace', $apiKey, $apiUrl);

        $products = [];
        foreach ($productSkuValues as $productId) {
            $products[] = ['productId' => $productId];
        }

        $session = [
            'sessionId' => $this->getSessionHash(),
        ];

        $customerId = $this->customerSession->getCustomerId();
        if ($customerId) {
            $session['customerId'] = $customerId;
        }

        try {
            $response = $sdk->create_auction(
                [
                    'listings' => intval($promotedProductsCount),
                ],
                $products,
                $session
            )->wait();
        } catch (\Topsort\TopsortException $e) {
            $prevException = $e->getPrevious();

            if ($prevException && $prevException instanceof \GuzzleHttp\Exception\ClientException) {
                $this->logger->critical($prevException);
                $this->logger->critical('TOPSORT_RESPONSE:' . (string)$prevException->getResponse()->getBody());
            }
            $this->logger->critical($e->getPrevious());
            return [];
        } catch (\Exception $e) {
            $this->logger->critical($e->getPrevious());
            return [];
        }
        $result = [];
        if (isset($response['slots']['listings']['winners'])) {
            foreach ($response['slots']['listings']['winners'] as $winner) {
                if (isset($winner['rank']) && isset($winner['productId'])) {
                    $result[$winner['rank']] = $winner['productId'];
                }
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    protected function getSessionHash()
    {
        return sha1(substr($this->customerSession->getSessionId(), 0, 8));
    }
}