<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Model\System\Config\Source;

class CurrencyMultiplier
{
    protected static $_options;

    public function toOptionArray()
    {

        if (!self::$_options) {

            self::$_options = array(
                array(
                    'label' => '1',
                    'value' => '1'
                ),
                array(
                    'label' => '10',
                    'value' => '10'
                ),
                array(
                    'label' => '100',
                    'value' => '100'
                ),
                array(
                    'label' => '1000',
                    'value' => '1000'
                )
            );
        }

        return self::$_options;
    }

}
