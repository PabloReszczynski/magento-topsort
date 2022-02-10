<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Controller\Search\Result;

class Index extends \Magento\CatalogSearch\Controller\Result\Index
{
    /**
     * @param \Magento\CatalogSearch\Controller\Result\Index $action
     * @param null $extraLayoutHandle
     * @return \Magento\Framework\App\ViewInterface
     */
    function executeLoadPromotionsAction($action, $extraLayoutHandle = null)
    {
        $view = $action->_view;
        $view->getPage()->initLayout();
        if ($extraLayoutHandle) {
            $view->getLayout()->getUpdate()->addHandle($extraLayoutHandle);
        }
        return $view;
    }
}