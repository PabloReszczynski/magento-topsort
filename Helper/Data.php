<?php

namespace Topsort\Integration\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONF_ENABLED = 'topsort_integration/api/enabled';
    const CONF_API_KEY = 'topsort_integration/api/key';
    const CONF_API_URL = 'topsort_integration/api/url';
    const CONF_CURRENCY_MULTIPLIER = 'topsort_integration/api/currency_multiplier';

    const CONF_SPONSORSHIP_ON_CATALOG_ENABLED = 'topsort_integration/catalog/enabled';
    const CONF_CATALOG_PROMOTED_PRODUCTS_AMOUNT = 'topsort_integration/catalog/promoted_products_amount';
    const CONF_CATALOG_MINIMUM_PRODUCTS_AMOUNT = 'topsort_integration/catalog/minimum_products_amount';
    const CONF_CATALOG_LABEL_TEXT = 'topsort_integration/catalog/label_text';

    const CONF_SPONSORSHIP_IN_SEARCH_ENABLED = 'topsort_integration/search/enabled';
    const CONF_SEARCH_PROMOTED_PRODUCTS_AMOUNT = 'topsort_integration/search/promoted_products_amount';
    const CONF_SEARCH_MINIMUM_PRODUCT_AMOUNT = 'topsort_integration/search/minimum_products_amount';
    const CONF_SEARCH_LABEL_TEXT = 'topsort_integration/search/label_text';


    function getApiKey()
    {
        return $this->scopeConfig->getValue(self::CONF_API_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    function getApiUrl()
    {
        return $this->scopeConfig->getValue(self::CONF_API_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    function getCurrencyMultiplier()
    {
        return $this->scopeConfig->getValue(self::CONF_CURRENCY_MULTIPLIER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    function getIsEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::CONF_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    function getIsEnabledOnCatalogPages()
    {
        return $this->scopeConfig->isSetFlag(self::CONF_SPONSORSHIP_ON_CATALOG_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    function getIsEnabledOnSearch()
    {
        return $this->scopeConfig->isSetFlag(self::CONF_SPONSORSHIP_IN_SEARCH_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    function getPromotedLabelTextForCatalogPages()
    {
        return $this->scopeConfig->getValue(self::CONF_CATALOG_LABEL_TEXT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    function getPromotedLabelTextForSearch()
    {
        return $this->scopeConfig->getValue(self::CONF_SEARCH_LABEL_TEXT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    function getMinProductsAmountForCatalogPages()
    {
        return $this->scopeConfig->getValue(self::CONF_CATALOG_MINIMUM_PRODUCTS_AMOUNT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    function getMinProductsAmountForSearch()
    {
        return $this->scopeConfig->getValue(self::CONF_SEARCH_MINIMUM_PRODUCT_AMOUNT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    function getPromotedProductsAmountForCatalogPages()
    {
        return $this->scopeConfig->getValue(self::CONF_CATALOG_PROMOTED_PRODUCTS_AMOUNT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    function getPromotedProductsAmountForSearch()
    {
        return $this->scopeConfig->getValue(self::CONF_SEARCH_PROMOTED_PRODUCTS_AMOUNT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}