<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Model;

use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class RefererUrl
{
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    function __construct(
        UrlFinderInterface $urlFinder,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\UrlInterface $url
    )
    {
        $this->urlFinder = $urlFinder;
        $this->redirect = $redirect;
        $this->url = $url;
    }

    function getRefererRouteData($referrerUrl)
    {
        if (!$referrerUrl) {
            $referrerUrl = $this->redirect->getRefererUrl();
        }
        // remove base URL part
        $baseUrl = $this->url->getBaseUrl();
        $urlPathWithQuery = str_replace($baseUrl, '', $referrerUrl);
        // remove query string part
        $urlPathWithQueryArray = explode('?', $urlPathWithQuery);
        $urlPath = current($urlPathWithQueryArray);

        $url = $this->urlFinder->findOneByData([
            //UrlRewrite::REQUEST_PATH => ltrim($_SERVER['REQUEST_URI'], '/'),
            UrlRewrite::REQUEST_PATH => $urlPath
        ]);
        $data = $url ? $url->toArray() : null;
        $data['url_path'] = $urlPath;
        return $data;
    }
}
