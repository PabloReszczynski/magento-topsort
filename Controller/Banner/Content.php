<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Controller\Banner;

use Magento\Framework\Controller\ResultFactory;

class Content extends \Magento\Framework\App\Action\Action
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

    function __construct(
        \Topsort\Integration\Model\ResourceModel\Banner\Grid\Collection $collection,
        \Topsort\Integration\Model\Api $api,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\App\Action\Context $context
    )
    {
        $this->collection = $collection;
        $this->api = $api;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $request = $this->getRequest();
        $bannerId = $request->getParam('id');

        $bannerData = $this->getBannerData($bannerId);
        $resultData = [];
        if ($bannerData !== false) {
            $resultData['html'] = '<a href="'
                . $bannerData['promoted_url'] . '"><img style="width: ' . intval($bannerData['width']) . 'px; height: ' . intval($bannerData['height']) . 'px" width="' . $bannerData['width']
                . '" height="' . $bannerData['height'] . '" alt="" src="' . $bannerData['image_url'] . '"/></a>';
        } else {
            $resultData['html'] = ''; // TODO what to show if no banners returned?
        }

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData($resultData);
        return $result;
    }

    private function getBannerData($bannerId)
    {
        $data = $this->collection->getBannerDataById($bannerId);
        // get banner url from API
        $bannersFromApi = $this->api->getBanners($data['placement']);
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
