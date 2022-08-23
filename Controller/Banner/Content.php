<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */

namespace Topsort\Integration\Controller\Banner;

use Magento\Framework\Controller\ResultFactory;

class Content extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Topsort\Integration\Helper\BannerHelper
     */
    private $bannerHelper;

    public function __construct(
        \Topsort\Integration\Helper\BannerHelper $bannerHelper,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->bannerHelper = $bannerHelper;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $request = $this->getRequest();
        $bannerId = $request->getParam('id');
        $searchQuery = $request->getParam('search');

        $resultData['html'] = $this->bannerHelper->getBannerHtml($bannerId, $searchQuery);

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData($resultData);
        return $result;
    }
}
