<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Model\ResourceModel\Banner\Grid;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Topsort\Integration\Model\Api;
use Topsort\Integration\Model\ResourceModel\Banner\Grid\Collection\Item;

/**
 * Collection for displaying grid of banners
 */
class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * @var Api
     */
    private $api;

    function __construct(
        EntityFactoryInterface $entityFactory,
        Api $api)
    {
        $this->api = $api;
        parent::__construct($entityFactory);
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return \Magento\Framework\Data\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        // TODO: Get Banner ad locations from DB

        if (empty($this->_items)) {
            $bannerAds = $this->api->getBannerAdLocations();
            $items = [];
            foreach ($bannerAds as $bannerAd) {
                $bannerAd['id'] = $this->getBannerIdForData($bannerAd['placement'], $bannerAd['width'], $bannerAd['height']);
                $items[] = new Item($bannerAd);
            }
            $this->_items = $items;
        }
        return $this;
    }

    function getBannerDataById($bannerId)
    {
        $data = explode('|', base64_decode($bannerId));
        $placement = $data[0] ?? 'unknown';
        $dimensions = $data[1] ?? 'unknown';
        $dimensionsArray = explode('x', $dimensions);

        $width = intval($dimensionsArray[0] ?? 1);
        $height = intval($dimensionsArray[1] ?? 1);

        if ($width == $height) {
          $aspectRatio = '1:1';
        } else {
          $gcd = gmp_gcd($width, $height);
          $numerator = intdiv($width, $gcd);
          $denominator = intdiv($height, $gcd);
          $aspectRatio = '' . $numerator . ':' . $denominator;
        }
        return [
            'id' => $bannerId,
            'placement' => $placement,
            'width' => $width,
            'height' => $height,
            'aspectRatio' => $aspectRatio,
        ];
    }

    function getBannerIdForData($placement, $width, $height)
    {
        return base64_encode($placement . '|' . $width . 'x' . $height);
    }
}
