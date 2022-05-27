<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Model\ResourceModel\Fulltext\Collection;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\DB\Select;
use Topsort\Integration\Model\Api;

class SearchResultApplier extends \Magento\Elasticsearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplier
{
    /**
     * @var Collection|\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
     */
    private $collection;

    /**
     * @var SearchResultInterface
     */
    private $searchResult;

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $currentPage;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var Api
     */
    private $topsortApi;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param Collection $collection
     * @param SearchResultInterface $searchResult
     * @param int $size
     * @param int $currentPage
     */
    public function __construct(
        Collection $collection,
        SearchResultInterface $searchResult,
        int $size,
        int $currentPage,
        Api $topsortApi,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    ) {
        $this->collection = $collection;
        $this->searchResult = $searchResult;
        $this->size = $size;
        $this->currentPage = $currentPage;

        $this->collectionFactory = $collectionFactory;
        $this->topsortApi = $topsortApi;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        parent::__construct($collection, $searchResult, $size, $currentPage);
    }
    /**
     * @inheritDoc
     */
    public function apply()
    {
        $collection = $this->collection;
        if ($collection->hasFlag('topsort_promotions_load_mode')
            && $collection->getFlag('topsort_promotions_load_mode') === true) {

            $this->applyWithPromotionsLoad();

        } else {
            parent::apply();
        }
    }

    protected function applyWithPromotionsLoad()
    {
        try {
            if (empty($this->searchResult->getItems())) {
                $this->collection->getSelect()->where('NULL');
                return;
            }
            $collection = $this->collection;
            $promotedProductsCount = $collection->getFlag('topsort_promotions_count');
            $preloadBannerData = $collection->getFlag('preload_banner_data');
            $skipRequest = $collection->getFlag('skip_promotions_request');
            if ($skipRequest) {
                $this->collection->getSelect()->where('NULL');
                return;
            }

            $ids = $this->getProductIds();
            $skuValues = $this->getSkuForIds($ids);

            $result = $this->topsortApi->getSponsoredProducts($skuValues, $promotedProductsCount, $preloadBannerData);
            $sponsoredItemSkuList = isset($result['products']) ? $result['products'] : [];
            $this->collection->setFlag('auction_id', isset($result['auction_id']) ? $result['auction_id'] : null);

            if (empty($sponsoredItemSkuList)) {
                $this->collection->getSelect()->where('NULL');
                return;
            }

            $sponsoredItemIdList = $this->getIdsForSkuList($sponsoredItemSkuList);

            if (empty($sponsoredItemIdList)) {
                $this->collection->getSelect()->where('NULL');
                return;
            }

            $this->collection->getSelect()
                ->where('e.entity_id IN (?)', $sponsoredItemIdList)
                ->reset(Select::ORDER);

            $orderList = join(',', $sponsoredItemIdList);
            $this->collection->getSelect()
                ->order(new \Zend_Db_Expr("FIELD(e.entity_id,$orderList)"));
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->collection->getSelect()->where('NULL');
            return;
        }
    }

    /**
     * @param array $ids
     * @return array
     */
    private function getSkuForIds($ids)
    {
        $skuValues = [];
        $collection = $this->collectionFactory->create();
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $collection->addFieldToSelect('sku');
        $con = $collection->getResource()->getConnection();
        $select = $collection->getSelect();
        $select->reset(Select::COLUMNS);
        $select->columns('sku');
        // request 200 skus within one SQL query
        foreach (array_chunk($ids, 200) as $idsSubset) {
            array_walk($idsSubset, 'intval');
            $select->reset(Select::WHERE);
            $select->where("e.entity_id IN (?)", $idsSubset);
            $skuValues = array_merge($skuValues, $con->fetchCol($select));
        }
        return $skuValues;
    }

    /**
     * @param array $skuValues
     * @return array
     */
    private function getIdsForSkuList($skuValues)
    {
        $ids = [];
        $collection = $this->collectionFactory->create();
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $collection->addFieldToSelect('sku');
        $con = $collection->getResource()->getConnection();
        $select = $collection->getSelect();
        $select->reset(Select::COLUMNS);
        $select->columns('entity_id');
        // request 100 skus within one SQL query
        foreach (array_chunk($skuValues, 100) as $skuValuesSubset) {
            // Note: for security reasons, SKU values should not be inserted as part of the SQL query without proper escaping
            $select->reset(Select::WHERE);
            $select->where("e.sku IN (?)", $skuValuesSubset);
            $ids = array_merge($ids, $con->fetchCol($select));
        }
        return $ids;
    }

    /**
     * @return array
     */
    protected function getProductIds()
    {
        $items = $this->searchResult->getItems();
        $ids = [];
        foreach ($items as $item) {
            $ids[] = (int)$item->getId();
        }
        return $ids;
    }
}