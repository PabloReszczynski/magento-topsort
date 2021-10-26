<?php

namespace Topsort\Integration\Model;

class Api
{
    /**
     * @var \Topsort\Integration\Helper\Data
     */
    private $helper;

    function __construct(
        \Topsort\Integration\Helper\Data $helper
    )
    {
        $this->helper = $helper;
    }


    function getSponsoredProducts($productSkuValues)
    {
        $apiKey = $this->helper->getApiKey();
        $apiUrl = $this->helper->getApiUrl();
        $sdk = new \Topsort\SDK('magento-marketplace', $apiKey, $apiUrl);

        $slots = [
            'listings' => 2,
        ];

        foreach ($productSkuValues as $productId) {
            $products[] = ['productId' => $productId];
        }

        $session = [
            // TODO handle session parameters
            "sessionId" => "12345",
        ];
        try {
            $response = $sdk->create_auction($slots, $products, $session)->wait();
        } catch (\Topsort\TopsortException $e) {
            $prevException = $e->getPrevious();

            if ($prevException && $prevException instanceof \GuzzleHttp\Exception\ClientException) {
                var_dump((string)$prevException->getRequest()->getUri());
                var_dump($prevException->getRequest()->getHeader('User-Agent'));
                var_dump($prevException->getRequest()->getHeader('Authorization'));
                var_dump((string)$prevException->getRequest()->getBody());
                var_dump((string)$prevException->getResponse()->getBody());
                exit;
            }
            var_export(get_class($e->getPrevious()));
            echo $e->getMessage();exit;
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
}