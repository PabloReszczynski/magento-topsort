<?php
namespace Topsort\Integration\Block;

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

/**
 * Class Banner
 * @package Topsort\Integration\Block
 * Banner block properties:
 * - html_id
 * - banner_id
 * - height
 * - width
 * - placement
 *
 * Methods:
 * @method string getBannerId()
 * @method string getPlacement()
 */
class Banner extends \Magento\Framework\View\Element\Template
{
    function _construct()
    {
        $this->setTemplate('Topsort_Integration::topsort/banner.phtml');
        parent::_construct();
    }

    /**
     * @return string
     */
    function getHtmlId()
    {
        if (!$this->hasData('html_id')) {
            $uniqueString = $this->getValidHtmlId($this->getBannerId());
            if (empty($uniqueString)) {
                $uniqueString = uniqid();
            }
            $this->setData('html_id', 'topsort-banner-' . $uniqueString);
        }
        return $this->getData('html_id');
    }

    /**
     * @return int
     */
    function getWidth()
    {
        return intval($this->getData('width'));
    }

    /**
     * @return int
     */
    function getHeight()
    {
        return intval($this->getData('height'));
    }

    /**
     * @var string $string
     * @return string
     */
    private function getValidHtmlId($string) {
        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);
        return $string;
    }
}