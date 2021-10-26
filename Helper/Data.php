<?php

namespace Topsort\Integration\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONF_API_KEY = 'topsort_integration/api/key';
    const CONF_API_URL = 'topsort_integration/api/url';

    function getApiKey()
    {
        return $this->scopeConfig->getValue(self::CONF_API_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    function getApiUrl()
    {
        return $this->scopeConfig->getValue(self::CONF_API_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}