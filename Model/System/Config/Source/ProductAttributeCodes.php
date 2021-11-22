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
namespace Topsort\Integration\Model\System\Config\Source;

use Magento\Framework\Exception\LocalizedException;

class ProductAttributeCodes
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    private $attributeFactory;

    protected static $_options;

    function __construct(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory
    )
    {
        $this->attributeFactory = $attributeFactory;
    }

    public function toOptionArray()
    {

        if (!self::$_options) {

            $attributeInfo = $this->attributeFactory->getCollection();
            $attributeInfo->addOrder('attribute_code');
            $attributes = [];
            foreach($attributeInfo as $attribute)
            {
                /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
                if ($attribute->getFrontendInput() === 'select') {
                    $attributeCode = $attribute->getAttributeCode();
                    $attributes[] = [
                        'label' => $attribute->getDefaultFrontendLabel() . ' (' . $attributeCode . ')',
                        'value' => $attributeCode
                    ];
                }
            }

            self::$_options = $attributes;
        }

        return self::$_options;
    }

}
