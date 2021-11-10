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

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

class Products extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{

    /**
     * @inheritDoc
     */
    public function execute()
    {
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
            echo 'url is not matching the base url';exit;
        }
        $path = trim(substr($requestUriParts[0], strlen($baseUrlMeta['path'])), '/');

        // validate Bearer header
        $authHeader = $this->getRequest()->getHeader('Authorization');
        if (!$authHeader) {
            echo 'No Authorization header';exit;
        }
        // TODO move the token into config
        $validToken = 'dfajgnpahdgprgjnfdkj4054375nmcnorythqe';
        $authHeaderParts = explode(' ', $authHeader);
        if (count($authHeaderParts) !== 2 || $authHeaderParts[0] != 'Bearer') {
            echo 'Invalid Authorization header';exit;
        }
        $token = $authHeaderParts[1];
        if ($token != $validToken) {
            echo 'Invalid token';exit;
        }
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if ($path === 'topsort/api/products') {
            if ($this->getRequest()->getMethod() !== 'POST') {
                echo 'Only post requests are supported';
            }

            // read and validate post request data
            $content = $this->getRequest()->getContent();
            try {
                $productSkuList = json_decode($content, 1);
                if (!is_array($productSkuList)) {
                    throw new \Exception('Body should contain an array of product ids');
                }
            } catch (\Exception $e) {
                echo 'Invalid body format. ' . $e->getMessage();
                exit;
            }

            // prepare mock response
            $products = [];
            foreach ($productSkuList as $sku) {
                $products[] = [
                    'id' => $sku,
                    'name' => 'Product with SKU ' . $sku,
                    'description' => 'Nombrada «Mejor cerveza del mundo» en 1998 en el Certamen Mundial de Cerveza de Chicago, Illinois, Estados Unidos, es una cerveza que seguro te encantará.',
                    'vendorID' => '9SiwYqqL8vdG',
                    'vendorName' => 'Huyghe Brewery',
                    'stock' => rand(126, 1000),
                    'price' => rand(14900, 24900),
                    'imageURL' => 'https://r.btcdn.co/r/eyJzaG9wX2lkIjozMzU4LCJnIjoiMjYweCJ9/1759e16e6314a24/669830-Cerveza_Delirium_Tremens_Botella_330cc_x6.png',
                    'brandID' => 'N8G6bGjS1YfF',
                    'brandName' => 'Delirium Tremens',
                    'categoryID' => 'ahEDqV5uhjj8',
                    'categoryName' => 'Cervezas/Oscuras/Triple Ale',
                ];
            }
            $result->setData($products);
        } else if ($path === 'topsort/api/products/search') {
            // handle the search API request
            $query = [];
            $queryPart = parse_str($queryPart, $query);
            $searchString = isset($query['search']) ? $query['search'] : '';
            $categoryString = isset($query['categoryID']) ? $query['categoryID'] : '';

            $productSkuList = [
                'productFromSearch1',
                'productFromSearch2',
                'productFromSearch3'
            ];

            $products = [];
            foreach ($productSkuList as $sku) {
                $products[] = [
                    'id' => $sku,
                    'name' => 'Product with SKU ' . $sku,
                    'description' => 'Product for search query "' . $searchString . '"',
                    'vendorID' => '9SiwYqqL8vdG',
                    'vendorName' => 'Huyghe Brewery',
                    'stock' => rand(126, 1000),
                    'price' => rand(14900, 24900),
                    'imageURL' => 'https://r.btcdn.co/r/eyJzaG9wX2lkIjozMzU4LCJnIjoiMjYweCJ9/1759e16e6314a24/669830-Cerveza_Delirium_Tremens_Botella_330cc_x6.png',
                    'brandID' => 'N8G6bGjS1YfF',
                    'brandName' => 'Delirium Tremens',
                    'categoryID' => isset($categoryString) ? $categoryString : 'ahEDqV5uhjj8',
                    'categoryName' => 'Cervezas/Oscuras/Triple Ale',
                ];
            }

            $result->setData($products);
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