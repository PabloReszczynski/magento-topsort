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
namespace Topsort\Integration\Controller\Api;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Vendors extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;
    /**
     * @var \Topsort\Integration\Helper\Data
     */
    private $dataHelper;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    function __construct(
        Context $context,
        \Magento\Eav\Model\Config $eavConfig,
        \Psr\Log\LoggerInterface $logger,
        \Topsort\Integration\Helper\Data $dataHelper
    )
    {
        $this->eavConfig = $eavConfig;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        // validate Bearer header
        if (!$this->dataHelper->validateApiAuthorization($result, $this->getRequest(), $this->getResponse())) {
            return $result;
        }

        $pageSize = $this->dataHelper->getCatalogResultsPageSize();

        // "prev" Page token
        $prev = intval($this->getRequest()->getParam('prev', 0));

        // "next" Page token
        $next = intval($this->getRequest()->getParam('next', 0));

        $newPrev = null;
        $newNext = null;

        $vendors = $this->getAllVendors();
        $pageNum = $prev > 0 ? $prev : ($next > 0 ? $next : 1);
        $resultList = [];
        $totalPages = ceil(count($vendors) / $pageSize);
        $vendors = array_slice($vendors, ($pageNum - 1) * $pageSize + 1, $pageSize, true);
        foreach ($vendors as $id => $name) {
            $resultList[] = [
                'id' => strval($id),
                'name' => $name,
                //'logoURL' => 'https://...'
            ];
        }

        if (empty($vendors)) {
            $result->setHttpResponseCode(404);
        }

        $result->setData([
            'response' => $resultList,
            'prev' => $pageNum == 1 ? null : min($pageNum - 1, $totalPages),
            'next' => $pageNum >= $totalPages ? null : $pageNum + 1
        ]);

        return $result;
    }

    /**
     * @return array
     */
    protected function getAllVendors()
    {
        $vendors = [];
        try {
            $vendorAttributeCode = $this->dataHelper->getTopsortVendorAttributeCode();
            $attribute = $this->eavConfig->getAttribute('catalog_product', $vendorAttributeCode);
            $options = $attribute->getSource()->getAllOptions();
            foreach ($options as $option) {

                $value = intval($option['value']);
                if (!$value) {
                    // do not return the "Not selected" option
                    continue;
                }
                $vendors[$value] = $option['label'];
            }
            asort($vendors);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $vendors;
    }
}