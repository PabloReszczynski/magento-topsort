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

namespace Topsort\Integration\Plugin\Product;

use Magento\Catalog\Model\Product;

class Url
{
    /**
     * @param Product\Url $urlModel
     * @param callable $proceed
     * @param Product $product
     * @param array $params
     * @return void
     */
    function aroundGetUrl(\Magento\Catalog\Model\Product\Url $urlModel, callable $proceed, Product $product, $params = [])
    {
        if ($product->getIsPromoted() === true) {
            if(!array_key_exists('_query', $params)){
                $params['_query'] = [];
            } else if (is_string($params['_query'])) {
                $params['_query'] = parse_str($params['_query']);
            } else if(!is_array($params['_query'])) {
                // unexpected query format. set it to array in order to be able to proceed
                $params['_query'] = [];
            }
            $params['_query']['auction_id'] = $product->getAuctionId();
        }
        return $proceed($product, $params);
    }
}