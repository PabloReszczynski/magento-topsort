<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;

/**
 * Banners controller
 */
abstract class Banner extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see \Magento\Backend\App\Action\_isAllowed()
     */
    const ADMIN_RESOURCE = 'Topsort_Integration::banner';
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Context $context)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
}
