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
declare(strict_types=1);

namespace Topsort\Integration\Plugin\Block\Product;

use Magento\Catalog\Model\Product;

class ImageFactory
{
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $actionContext;
    /**
     * @var \Topsort\Integration\Helper\Data
     */
    private $helperData;

    function __construct(
        \Topsort\Integration\Helper\Data $helperData,
        \Magento\Framework\App\Action\Context $actionContext
    )
    {
        $this->actionContext = $actionContext;
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Catalog\Block\Product\ImageFactory $factory
     * @param callable $proceed
     * @param Product $product
     * @param mixed ...$args
     * @return \Magento\Catalog\Block\Product\Image
     */
    function aroundCreate(\Magento\Catalog\Block\Product\ImageFactory $factory, callable $proceed, Product $product, ...$args)
    {
        $action = $this->actionContext->getRequest()->getFullActionName();
        $h = $this->helperData;
        $labelText = 'Promoted';//default value
        if ($action == 'catalog_category_view' && $h->getIsEnabledOnCatalogPages()) {
            $labelText = $h->getPromotedLabelTextForCatalogPages();
        } else if ($action == 'catalogsearch_result_index' && $h->getIsEnabledOnSearch()) {
            $labelText = $h->getPromotedLabelTextForSearch();
        }

        /** @var \Magento\Catalog\Block\Product\Image $image */
        $image = $proceed($product, ...$args);
        if ($product->getIsPromoted() === true) {
            $image->setIsPromoted(true);
            $image->setPromotedLabelText(__($labelText));
            $image->setAuctionId($product->getAuctionId());
        }
        $image->setTemplate('Topsort_Integration::product/image_with_borders.phtml');
        return $image;
    }

}