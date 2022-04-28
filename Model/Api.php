<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Model;

use GuzzleHttp\Exception\ClientException;
use Magento\Framework\Exception\LocalizedException;
use Topsort\TopsortException;

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
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    static private $bannerAdsData = null;

    function __construct(
        \Topsort\Integration\Helper\Data $helper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
    }

    function getSponsoredBanners($placement, $productSkuValues = [])
    {
        if (!$this->helper->getIsEnabled()) {
            return [];
        }
        $sdk = $this->getProvider();

        $products = [];
        foreach ($productSkuValues as $productId) {
            $products[] = ['productId' => $productId];
        }

        try {
            if ($placement === 'Category-page' && self::$bannerAdsData !== null) {
                // use cached result from promoted products request
                $result = ['slots' => ['bannerAds' => self::$bannerAdsData]];
            } else {
                $result = $sdk->create_auction(
                    [
                        'listings' => 1,
                        'bannerAds' => 1
                    ],
                    $products,
                    $this->getSessionData(),
                    [
                        'placement' => $placement
                    ]
                )->wait();
            }

            $this->logger->debug("TOPSORT: Banner Auction.\nRequest products count: " . count($products) . "\nResponse: " . $this->jsonHelper->jsonEncode($result));

        } catch (TopsortException $e) {
            $prevException = $e->getPrevious();
            if ($prevException && $prevException instanceof ClientException) {
                $this->logger->critical($prevException);
                $this->logger->critical('TOPSORT_RESPONSE:' . (string)$prevException->getResponse()->getBody());
            }
            $this->logger->critical($e->getPrevious());
            return [];
        } catch (\Exception $e) {
            $this->logger->critical($e->getPrevious());
            return [];
        }
        $winnersList = [];
        $auctionId = null;
        if (isset($result['slots']['bannerAds']['winners'], $result['slots']['bannerAds']['auctionId'])) {
            foreach ($result['slots']['bannerAds']['winners'] as $winner) {
                if (isset($winner['rank']) && isset($winner['productId'])) {
                    $winnersList[$winner['rank']] = [
                        'sku' => $winner['productId'] ?? $winner['winnerId'] ?? '',
                        'url' => $winner['assetUrl'] ?? '#',
                        'winnerType' => $winner['winnerType'] ?? '',
                        'winnerId' => $winner['winnerId'] ?? '',
                        // $winner['resolvedBidId'] not used
                    ];
                }
            }
            $auctionId = $result['slots']['bannerAds']['auctionId'];
        }

        return [
            'banners' => $winnersList,
            'auction_id' => $auctionId
        ];
    }

    function getSponsoredProducts($productSkuValues, $promotedProductsCount, $preloadBannerData = false)
    {
        if (!$this->helper->getIsEnabled()) {
            return [];
        }
        $sdk = $this->getProvider();

        $products = [];
        foreach ($productSkuValues as $productId) {
            $products[] = ['productId' => $productId];
        }

        try {
            $bannerOptions = null;
            $slots = [
                'listings' => intval($promotedProductsCount),
            ];
            if ($preloadBannerData) {
                $slots['bannerAds'] = 1;
                $bannerOptions['placement'] = 'Category-page';
            }
            $result = $sdk->create_auction(
                $slots,
                $products,
                $this->getSessionData(),
                $bannerOptions
            )->wait();
            $this->logger->debug("TOPSORT: Auction.\nRequest products count: " . count($products) . "\nResponse: " . $this->jsonHelper->jsonEncode($result));

        } catch (TopsortException $e) {
            $prevException = $e->getPrevious();

            if ($prevException && $prevException instanceof ClientException) {
                $this->logger->critical($prevException);
                $this->logger->critical('TOPSORT_RESPONSE:' . (string)$prevException->getResponse()->getBody());
            }
            $this->logger->critical($e->getPrevious());
            return [];
        } catch (\Exception $e) {
            $this->logger->critical($e->getPrevious());
            return [];
        }
        $winnersList = [];
        $auctionId = null;
        if (isset($result['slots']['listings']['winners'], $result['slots']['listings']['auctionId'])) {
            foreach ($result['slots']['listings']['winners'] as $winner) {
                if (isset($winner['rank']) && isset($winner['productId'])) {
                    $winnersList[$winner['rank']] = $winner['productId'];
                }
            }
            $auctionId = $result['slots']['listings']['auctionId'];
        }

        if (isset($result['slots']['bannerAds'])) {
            // let bannerAds be reused later during the next getBanners() call
            self::$bannerAdsData = $result['slots']['bannerAds'];
        }
        return [
            'products' => $winnersList,
            'auction_id' => $auctionId
        ];
    }

    function trackImpressions($page, $impressions)
    {
        if (empty($impressions) || !$this->helper->getIsEnabled()) {
            return [];
        }
        try {
            $count = 0;
            $apiImpressions = [];
            foreach ($impressions as $impression) {
                if (!isset($impression['sku'])) {
                    throw new LocalizedException(__('Internal error: incomplete data provided for trackImpressions() method.'));
                }
                $apiImpression = [
                    'placement' => [
                        'page' => $page,
                        'location' => 'position_' . ++$count
                    ],
                    'productId' => $impression['sku'],
                ];
                if (isset($impression['auction_id'])) {
                    $apiImpression['auctionId'] = $impression['auction_id'];
                }
                if (isset($impression['id'])) {
                    $apiImpression['id'] = $impression['id'];
                }
                $apiImpressions[] = $apiImpression;
            }

            $data = [
                'session' => $this->getSessionData(),
                'impressions' => $apiImpressions
            ];
            $result = $this->getProvider()->report_impressions($data)->wait();
            $this->logger->info('TOPSORT: Impressions tracking. ' . count($data['impressions']) . ' impressions were sent to Topsort.');
            $this->logger->debug("TOPSORT: Impressions tracking.\nRequest: " . $this->jsonHelper->jsonEncode($data) . "\nResponse: " . $this->jsonHelper->jsonEncode($result));
            return $result;
        } catch (TopsortException $e) {
            $prevException = $e->getPrevious();

            if ($prevException && $prevException instanceof ClientException) {
                $this->logger->critical($prevException);
                if (isset($data)) {
                    $this->logger->critical('TOPSORT_REQUEST:' . $this->jsonHelper->jsonEncode($data));
                }
                $this->logger->critical('TOPSORT_RESPONSE:' . (string)$prevException->getResponse()->getBody());
            }
            $this->logger->critical($e->getPrevious());
            return [];
        } catch (\Exception $e) {
            $this->logger->critical($e);
            if ($e->getPrevious()) {
                $this->logger->critical($e->getPrevious());
            }
            return [];
        }
    }

    function trackPurchase($orderNumber, $quoteId, $items)
    {
        if (!$this->helper->getIsEnabled()) {
            return [];
        }
        $sdk = $this->getProvider();

        try {
            $session = $this->getSessionData();
            $session['orderIntentId'] = $quoteId;
            $session['orderId'] = $orderNumber;

            $apiItems = [];

            foreach ($items as $item) {
                if (!isset($item['sku'], $item['price'], $item['quantity'])) {
                    throw new LocalizedException(__('Internal error: incomplete data provided for trackPurchase() method.'));
                }
                $apiItem = [
                    'productId' => $item['sku'],
                    'quantity' => intval($item['quantity']),
                    'unitPrice' => $this->convertPrice($item['price'])
                ];
                if (!empty($item['auction_id'])) {
                    $apiItem['auctionId'] = $item['auction_id'];
                }
                $apiItems[] = $apiItem;
            }

            $data = [
                'session' => $session,
                'items' => $apiItems,
                'purchasedAt' => new \DateTime(),
                'id' => $orderNumber
            ];

            $result = $sdk->report_purchase($data)->wait();
            $this->logger->info('TOPSORT: Purchase tracking. Invoice ' . $orderNumber . ' was sent to Topsort.');
            $this->logger->debug("TOPSORT: Purchase tracking.\nRequest: " . $this->jsonHelper->jsonEncode($data) . "\nResponse: " . $this->jsonHelper->jsonEncode($result));
            return $result;
        } catch (TopsortException $e) {
            $prevException = $e->getPrevious();

            if ($prevException && $prevException instanceof ClientException) {
                if (isset($data)) {
                    $this->logger->critical('TOPSORT_REQUEST:' . $this->jsonHelper->jsonEncode($data));
                }
                $this->logger->critical($prevException);
                $this->logger->critical('TOPSORT_RESPONSE:' . (string)$prevException->getResponse()->getBody());
            }
            $this->logger->critical($e->getPrevious());
            return [];
        } catch (\Exception $e) {
            $this->logger->critical($e->getPrevious());
            return [];
        }
    }

    public function trackProductClick($page, $position, $productSku, $auctionId = null, $id = null)
    {
        if (!$this->helper->getIsEnabled()) {
            return [];
        }
        try {
            $data = [
                'session' => $this->getSessionData(),
                'placement' => [
                    'page' => $page,
                    'location' => 'position_' . intval($position)
                ],
                'productId' => $productSku
            ];
            if (!empty($auctionId)) {
                $data['auctionId'] = $auctionId;
            }
            if (!empty($id)) {
                $data['id'] = $id;
            }
            $result = $this->getProvider()->report_click($data)->wait();
            $this->logger->info('TOPSORT: Click tracking. Product page request for product sku ' . $productSku . ' was reported to Topsort.');
            $this->logger->debug("TOPSORT: Click tracking.\nRequest: " . $this->jsonHelper->jsonEncode($data) . "\nResponse: " . $this->jsonHelper->jsonEncode($result));
            return $result;
        } catch (TopsortException $e) {
            $prevException = $e->getPrevious();

            if ($prevException && $prevException instanceof ClientException) {
                if (isset($data)) {
                    $this->logger->critical('TOPSORT_REQUEST:' . $this->jsonHelper->jsonEncode($data));
                }
                $this->logger->critical($prevException);
                $this->logger->critical('TOPSORT_RESPONSE:' . (string)$prevException->getResponse()->getBody());
            }
            $this->logger->critical($e->getPrevious());
            return [];
        } catch (\Exception $e) {
            $this->logger->critical($e->getPrevious());
            return [];
        }
    }

    public function getBannerAdLocations()
    {
        try {
            $sdk = $this->getAdsApiProvider();
            $result = $sdk->get_ad_locations()->wait();
            $bannerAds = [];
            foreach (($result['bannerAds'] ?? []) as $bannerAd) {
                $bannerAds[] = [
                    'width' => $bannerAd['dimensions']['width'] ?? 0,
                    'height' => $bannerAd['dimensions']['height'] ?? 0,
                    'placement' => $bannerAd['placement']['page'],
                ];
            }
            return $bannerAds;
        } catch (TopsortException $e) {
            $prevException = $e->getPrevious();
            if ($prevException && $prevException instanceof ClientException) {
                $this->logger->critical($prevException);
                $this->logger->critical('TOPSORT_RESPONSE:' . (string)$prevException->getResponse()->getBody());
            }
            $this->logger->critical($e->getPrevious());
            return [];
        } catch (\Exception $e) {
            $this->logger->critical($e->getPrevious());
            return [];
        }
    }

    /**
     * @return string
     */
    protected function getSessionHash()
    {
        return sha1(substr($this->customerSession->getSessionId(), 0, 8));
    }

    /**
     * @return \Topsort\SDK
     */
    protected function getProvider()
    {
        $apiKey = $this->helper->getApiKey();
        $apiUrl = $this->helper->getApiUrl();
        return new \Topsort\SDK('magento-marketplace', $apiKey, $apiUrl);
    }

    /**
     * @return \Topsort\SDK
     */
    protected function getAdsApiProvider()
    {
        $apiKey = $this->helper->getApiKey();
        $apiUrl = $this->helper->getApiUrl();
        return new \Topsort\SDK('magento-marketplace', $apiKey, str_replace('.api.', '.app.', $apiUrl));
    }

    /**
     * @return array
     */
    protected function getSessionData()
    {
        $session = [
            'sessionId' => $this->getSessionHash(),
        ];

        $customerId = $this->customerSession->getCustomerId();
        if ($customerId) {
            $session['customerId'] = $customerId;
        }
        return $session;
    }

    private function convertPrice($price)
    {
        $multiplier = $this->helper->getCurrencyMultiplier();
        return intval($price*$multiplier);
    }
}
