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
namespace Topsort\Integration\Controller\Api;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

class Products extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;
    /**
     * @var \Topsort\Integration\Helper\CatalogHelper
     */
    private $catalogHelper;
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    private $categoryFactory;
    /**
     * @var \Topsort\Integration\Helper\ProductImageHelper
     */
    private $productImageHelper;
    /**
     * @var \Topsort\Integration\Helper\SearchHelper
     */
    private $searchHelper;
    /**
     * @var \Topsort\Integration\Helper\Data
     */
    private $dataHelper;

    function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Topsort\Integration\Helper\Data $dataHelper,
        \Topsort\Integration\Helper\CatalogHelper $catalogHelper,
        \Topsort\Integration\Helper\ProductImageHelper $productImageHelper,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Topsort\Integration\Helper\SearchHelper $searchHelper,
        Context $context
    )
    {
        $this->productRepository = $productRepository;
        $this->eavConfig = $eavConfig;
        $this->catalogHelper = $catalogHelper;
        $this->productImageHelper = $productImageHelper;
        $this->categoryFactory = $categoryFactory;
        $this->searchHelper = $searchHelper;
        $this->dataHelper = $dataHelper;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        // ger URL current path
        /** @var \Magento\Framework\UrlInterface $url */
        $url = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\UrlInterface');
        $requestUri = $this->getRequest()->getRequestUri();
        $requestUriParts = explode('?', $requestUri);
        $path = $requestUriParts[0];
        $queryPart = isset($requestUriParts[1]) ? $requestUriParts[1] : '';
        $baseUrlMeta = parse_url($url->getBaseUrl());
        if (substr($path, 0, strlen($baseUrlMeta['path'])) !== $baseUrlMeta['path']) {
            $result->setHttpResponseCode(400);
            $result->setData(['error' => 'Wrong URL. Url is not matching the configured base url.']);
            return $result;
        }
        $path = trim(substr($requestUriParts[0], strlen($baseUrlMeta['path'])), '/');

        // validate Bearer header
        if (!$this->dataHelper->validateApiAuthorization($result, $this->getRequest(), $this->getResponse())) {
            return $result;
        }

        $attribute = $this->eavConfig->getAttribute('catalog_product', $this->dataHelper->getTopsortVendorAttributeCode());
        $source = $attribute->getSource();

        if ($path === 'topsort/api/products') {
            if ($this->getRequest()->getMethod() !== 'POST') {
                $result->setHttpResponseCode(400);
                $result->setData(['error' => 'Only post requests are supported']);
                return $result;
            }

            // read and validate post request data
            $content = $this->getRequest()->getContent();
            try {
                $productSkuList = json_decode($content, 1);
                if (!is_array($productSkuList)) {
                    throw new \Exception('Body should contain an array of product ids');
                }
            } catch (\Exception $e) {
                $result->setHttpResponseCode(400);
                $result->setData(['error' => 'Invalid body format. ' . $e->getMessage()]);
                return $result;
            }

            $products = [];
            foreach ($productSkuList as $sku) {

                try {
                    /** @var Product $product */
                    $product = $this->productRepository->get($sku);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    // if the product was not found do nothing
                    continue;
                }

                $vendorId = $product->getData($this->dataHelper->getTopsortVendorAttributeCode());
                $vendorName = $source->getOptionText($vendorId);
                $categoryIds = $product->getCategoryIds();
                /** @var \Magento\Catalog\Model\Category $category */
                $category = $this->categoryFactory->create();
                $categoryId = isset($categoryIds[0]) ? $categoryIds[0] : 0; // TODO note: only first category from this list is returned
                $category->load($categoryId);
                try {
                    $imageUrl = $this->productImageHelper->getImageUrl($product);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    // image not found
                    $imageUrl = "";
                }
                $productData = [
                    'id' => $sku,
                    'name' => $product->getName(),
                    'description' => $product->getShortDescription(),
                    'vendorID' => intval($vendorId),
                    'vendorName' => $vendorName ? $vendorName : "",
                    'stock' => $this->catalogHelper->getStockQty($product->getId()),
                    'price' => $product->getPrice(),
                    'imageURL' => $imageUrl,
                    'brandID' => intval($vendorId),
                    'brandName' => $vendorName ? $vendorName : "",
                    'categoryID' => $categoryId,
                    'categoryName' => $this->catalogHelper->getCategoryFullName($category)
                ];
                $products[] = $productData;
            }
            $result->setData($products);
        } else if ($path === 'topsort/api/products/search') {
            // handle the search API request
            $query = [];
            parse_str($queryPart, $query);
            // The search string provided by the user. When a blank string is provided, the string
            // should match all products (e.g. all products with a given category ID).
            $searchString = isset($query['search']) ? $query['search'] : '';
            // Only retrieve products whose category matches the provided ID.
            $categoryString = isset($query['categoryID']) ? $query['categoryID'] : '';
            // "prev" Page token
            $prev = intval($this->getRequest()->getParam('prev', 0));

            // "next" Page token
            $next = intval($this->getRequest()->getParam('next', 0));

            $pageNum = $prev > 0 ? $prev : ($next > 0 ? $next : 1);

            $pageSize = $this->dataHelper->getCatalogRequestPageSize();
            $productsSearch = $this->searchHelper->searchProducts($searchString, $categoryString, $pageNum, $pageSize);
            //var_export($productsSearch->count());exit;
            $lastPage = $productsSearch->getLastPageNumber();

            if ($pageNum > $lastPage) {
                $result->setHttpResponseCode(404);
                $result->setData([
                    'prev' => null,
                    'next' => $lastPage,
                    'response' => []
                ]);
                return $result;
            }

            $products = [];
            $count = 0;
            $newNext = null;
            $minId = null;
            foreach ($productsSearch as $product) {
                /** @var Product $product */
                $count++;
                $id = $product->getId();
                $minId = ($minId === null || $id < $minId) ? $id : $minId;
                if ($count > $pageSize) {
                    $newNext = $product->getId();
                    break;
                }
                $vendorId = $product->getData($this->dataHelper->getTopsortVendorAttributeCode());
                $vendorName = $source->getOptionText($vendorId);
                $categoryIds = $product->getCategoryIds();
                /** @var \Magento\Catalog\Model\Category $category */
                $category = $this->categoryFactory->create();
                $categoryId = isset($categoryIds[0]) ? $categoryIds[0] : 0; // TODO note: only first category from this list is returned
                $category->load($categoryId);
                try {
                    $imageUrl = $this->productImageHelper->getImageUrl($product);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    // image not found
                    $imageUrl = "";
                }
                $sku = $product->getSku();
                $productData = [
                    'id' => $sku,
                    'name' => $product->getName(),
                    'description' => $product->getShortDescription(),
                    'vendorID' => intval($vendorId),
                    'vendorName' => $vendorName ? $vendorName : "",
                    'stock' => $this->catalogHelper->getStockQty($product->getId()),
                    'price' => $product->getPrice(),
                    'imageURL' => $imageUrl,
                    'brandID' => intval($vendorId),
                    'brandName' => $vendorName ? $vendorName : "",
                    'categoryID' => $categoryId,
                    'categoryName' => $this->catalogHelper->getCategoryFullName($category)
                ];
                $products[] = $productData;
            }
            if (empty($products)) {
                $result->setHttpResponseCode(404);
            }
            $resultData = [
                'prev' => $pageNum > 1 ? $pageNum - 1 : null,
                'next' => $pageNum >= $lastPage ? null : $pageNum + 1,
                'response' => $products
            ];
            if (!empty($prev)) {
                $resultData['prev'] = $minId;
            }
            if (!empty($newNext)) {
                $resultData['next'] = $newNext;
            }
            $result->setData($resultData);
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}