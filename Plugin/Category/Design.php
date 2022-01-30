<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
declare(strict_types=1);

namespace Topsort\Integration\Plugin\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;

class Design
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    function __construct(
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->request = $request;
    }

    /**
     * @param \Magento\Catalog\Model\Design $actionModel
     * @param callable $proceed
     * @param Category|Product $object
     * @return DataObject
     */
    function aroundGetDesignSettings(\Magento\Catalog\Model\Design $actionModel, callable $proceed, $object)
    {
        /** @var DataObject $settings */
        $settings = $proceed($object);
        if ($object instanceof Category && $this->request->getParam('load-promotions')) {
            $layoutHandles = $settings->getPageLayoutHandles();
            $layoutHandles = $layoutHandles ? $layoutHandles : [];
            $layoutHandles['topsort'] = 'promotions';

            $settings->setPageLayoutHandles($layoutHandles);
        }
        return $settings;
    }
}