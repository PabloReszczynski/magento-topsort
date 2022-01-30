<?php
namespace Topsort\Integration\Block;
use Magento\Framework\View\Element\Template;

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
class PromotedProductsLoader extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    function __construct(
        \Magento\Framework\Registry $registry,
        Template\Context $context,
        array $data = [])
    {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    function getLoaderIconUrl()
    {
        return $this->getViewFileUrl('images/loader-2.gif');
    }

    function getLoadPromotionsUrl()
    {
        return $this->getUrl('*/*/*', [
            'id' => $this->getCategoryId(),
            'load-promotions' => 1
        ]);
//        return $this->getUrl('topsort/category/loadPromotedProducts', [
//            'id' => $this->getCategoryId()
//        ]);
    }

    private function getCategoryId()
    {
        return $this->getCurrentCategory()->getId();
    }

    /**
     * Retrieve current category model object
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', $this->_coreRegistry->registry('current_category'));
        }
        return $this->getData('current_category');
    }
}