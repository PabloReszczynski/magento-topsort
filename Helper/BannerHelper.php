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

    public function getBannerHtml($bannerId)
    {
        $bannerData = $this->getBannerData($bannerId);
        $html = '';
        if ($bannerData !== false) {
            $html = '<div>'
                . '<pre>'
                .  json_encode($bannerData)
                . '</pre>'
                . '<a href="'
                . $bannerData['promoted_url'] . '"><img crossorigin="" style="width: ' . intval($bannerData['width']) . 'px; height: ' . intval($bannerData['height']) . 'px" width="' . $bannerData['width']
                . '" height="' . $bannerData['height'] . '" alt="" src="' . $bannerData['image_url'] . '"/></a>'
                . '</div>';
        } else {
            // TODO what to show if no banners returned?
            $html = '<p>NO DATA</p>';
        }
        return $html;
    }

    private function getBannerData($bannerId)
    {
        $data = $this->collection->getBannerDataById($bannerId);
        // get banner url from API
        $bannersFromApi = $this->api->getSponsoredBanners($data);
        if (empty($bannersFromApi['banners'])) {
            return false;
        }
        $auctionBannerData = array_shift($bannersFromApi['banners']);

        $data['image_url'] = $auctionBannerData['url'];

        $product = $this->productRepository->get($auctionBannerData['sku']);

        if ($product->isObjectNew()) {
            // product not found
            return false;
        }

        $data['promoted_url'] = $product->getProductUrl() . '?auctionId=' . $bannersFromApi['auction_id'];

        return $data;
    }
}
