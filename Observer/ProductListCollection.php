<?php

namespace Topsort\Integration\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ObserverInterface;
use Topsort\Integration\Model\Api;
use Topsort\Integration\Model\Product\CollectionHelper;

class ProductListCollection implements ObserverInterface
{
    /**
     * @var CollectionHelper
     */
    private $collectionHelper;
    /**
     * @var Api
     */
    private $topsortApi;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    function __construct(
        CollectionHelper $collectionHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        Api $topsortApi
    )
    {

        $this->collectionHelper = $collectionHelper;
        $this->topsortApi = $topsortApi;
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $observer->getData('collection');


        $allSku = $this->collectionHelper->getAllSku($collection);
        //$allSku = ['4RfbhmIx'];

        $sponsoredItemSkuList = $this->topsortApi->getSponsoredProducts($allSku);
        $sponsoredItemsList = [];

        foreach ($sponsoredItemSkuList as $sponsoredItemSku) {
            /** @var Product $product */
            try {
                $product = $this->productRepository->get($sponsoredItemSku);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                // if the product was not found do nothing
                continue;
            }
            $sponsoredItemsList[] = $product;
        }

        //$pageSize = $collection->getPageSize();
        //$count = 0;
        if ($sponsoredItemSkuList) {
            $items = $collection->getItems();
            foreach ($collection as $key => $item) {
                $collection->removeItemByKey($key);
            }

            // add new items
            foreach ($sponsoredItemsList as $item) {
                //$count++;
                $id = $item->getId();
                $item->setIsPromoted(true);
                $item->setId($id . '-promoted');
                $collection->addItem($item);
                $item->setId($id);
            }

            // re-add $items
            foreach ($items as $item) {
//                if ($count >= $pageSize) {
//                    break;
//                }
                //$count++;
                $collection->addItem($item);
            }
        }

    }
}