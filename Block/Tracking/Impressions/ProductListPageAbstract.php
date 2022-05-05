<?php
namespace Topsort\Integration\Block\Tracking\Impressions;

use Magento\Framework\Exception\LocalizedException;

/**
 * Topsort Magento Extension
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
abstract class ProductListPageAbstract extends \Magento\Framework\View\Element\Template
{
    /**
     * @return bool|\Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    function getProductCollection()
    {
        /** @var \Magento\Catalog\Block\Product\ListProduct $block */
        try {
            $block = $this->getLayout()->getBlock($this->getProductListBlock());
            return $block->getLoadedProductCollection();
        } catch (LocalizedException $e) {
            return false;
        }
    }

    public function getImpressionsTrackingUrl()
    {
        $productIds = [];
        /** @var bool|\Magento\Eav\Model\Entity\Collection\AbstractCollection $productsCollection */
        $productsCollection = $this->getProductCollection();
        // do not trigger collection from this block since this may cause some bugs on category page
        // normally, the collection should already be loaded, since this block is placed after the list of products
        if ($productsCollection && $productsCollection->isLoaded()) {

            foreach ($productsCollection as $product) {
                $productIds[] = $product->getId();
            }
        }

        return $this->getUrl('topsort/tracking/impressions', [
            'ids' => implode(',', $productIds),
            'page' => $this->getRequest()->getFullActionName()
        ]);
    }

    /**
     * @return string
     */
    abstract protected function getProductListBlock();
}