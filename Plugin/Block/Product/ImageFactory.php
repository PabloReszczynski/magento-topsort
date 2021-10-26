<?php
declare(strict_types=1);

namespace Topsort\Integration\Plugin\Block\Product;

use Magento\Catalog\Model\Product;

class ImageFactory
{
    /**
     * @param \Magento\Catalog\Block\Product\ImageFactory $factory
     * @param callable $proceed
     * @param Product $product
     * @param mixed ...$args
     * @return \Magento\Catalog\Block\Product\Image
     */
    function aroundCreate(\Magento\Catalog\Block\Product\ImageFactory $factory, callable $proceed, Product $product, ...$args)
    {
        /** @var \Magento\Catalog\Block\Product\Image $image */
        $image = $proceed($product, ...$args);
        if ($product->getIsPromoted() === true) {
            $image->setIsPromoted(true);
        }
        $image->setTemplate('Topsort_Integration::product/image_with_borders.phtml');
        return $image;
    }

}