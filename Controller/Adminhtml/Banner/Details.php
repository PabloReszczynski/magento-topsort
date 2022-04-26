<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Controller\Adminhtml\Banner;

use Topsort\Integration\Controller\Adminhtml\Banner;

class Details extends Banner
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Topsort_Integration::banner');
        $resultPage->getConfig()->getTitle()->prepend(__('Topsort Banner Details'));
        $resultPage->addBreadcrumb(__('Topsort Banners'), __('Topsort Banners'));
        $resultPage->addBreadcrumb(__('Banner Details'), __('Banner Details'));
        return $resultPage;
    }
}
