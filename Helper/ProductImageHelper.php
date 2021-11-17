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
namespace Topsort\Integration\Helper;

use Magento\Framework\App\Helper\Context;

class ProductImageHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $imageHelperFactory;

    function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        Context $context
    )
    {
        $this->storeManager = $storeManager;
        $this->appEmulation = $appEmulation;
        $this->imageHelperFactory = $imageHelperFactory;
        parent::__construct($context);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    function getImageUrl($product)
    {
        // get the store ID from somewhere (maybe a specific store?)
        $storeId = $this->storeManager->getStore()->getId();
        // emulate the frontend environment
        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);

        // now the image helper will get the correct URL with the frontend environment emulated
        $imageUrl = $this->imageHelperFactory->create()
            ->init($product, 'product_thumbnail_image')->getUrl();
        // end emulation
        $this->appEmulation->stopEnvironmentEmulation();

        return $imageUrl;
    }
}