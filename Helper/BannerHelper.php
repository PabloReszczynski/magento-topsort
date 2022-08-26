<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */

namespace Topsort\Integration\Helper;

use Magento\Framework\App\Helper\Context;

class BannerHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Topsort\Integration\Model\ResourceModel\Banner\Grid\Collection
     */
    private $collection;
    /**
     * @var \Topsort\Integration\Model\Api
     */
    private $api;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    public function __construct(
        \Topsort\Integration\Model\ResourceModel\Banner\Grid\Collection $collection,
        \Topsort\Integration\Model\Api $api,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        Context $context
    ) {
        $this->collection = $collection;
        $this->api = $api;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    public function getBannerHtml($bannerId, $searchQuery)
    {
        $bannerData = $this->getBannerData($bannerId, $searchQuery);
        $html = '';
        if ($bannerData !== false) {
            $html = '<a href="'
                . $bannerData['promoted_url']
                . '"><img crossorigin="" style="width: 100%; height: auto" '
                . 'width="' . $bannerData['width']
                . '" height="' . $bannerData['height']
                . '" alt="" src="' . $bannerData['image_url']
                . '"/></a>';
        } else {
            // TODO what to show if no banners returned?
            // $html = '<p>NO DATA: <pre>' . json_encode($bannerData) . '</pre></p>';
        }
        return $html;
    }

    private function getBannerData($bannerId, $searchQuery)
    {
        $data = $this->collection->getBannerDataById($bannerId);
        // get banner url from API
        if (!empty($searchQuery)) {
            $bannersFromApi["searchQuery"] = $searchQuery;
        }
        $bannersFromApi = $this->api->getSponsoredBanners($data);
        if (empty($bannersFromApi['banners'])) {
            return false;
        }
        $auctionBannerData = array_shift($bannersFromApi['banners']);

        $data['image_url'] = $auctionBannerData['url'];

        $promoted_url = "#";
        if ($auctionBannerData['winnerType'] == 'product') {
            $product = $this->productRepository->get($auctionBannerData['sku']);
            if (!$product->isObjectNew()) {
                $promoted_url = $product->getProductUrl() . '?auctionId=' . $bannersFromApi['auction_id'];
            }
        } else {
            $promoted_url = 'men/tops-men/jackets-men.html';
        }

        $data['promoted_url'] = $promoted_url;

        return $data;
    }
}
