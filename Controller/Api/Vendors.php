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

    function __construct(
        Context $context,
        \Magento\Eav\Model\Config $eavConfig
    )
    {
        $this->eavConfig = $eavConfig;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        // validate Bearer header
        $authHeader = $this->getRequest()->getHeader('Authorization');
        if (!$authHeader) {
            echo 'No Authorization header';exit;
        }
        // TODO move the token into config
        $validToken = 'dfajgnpahdgprgjnfdkj4054375nmcnorythqe';
        $authHeaderParts = explode(' ', $authHeader);
        if (count($authHeaderParts) !== 2 || $authHeaderParts[0] != 'Bearer') {
            echo 'Invalid Authorization header';exit;
        }
        $token = $authHeaderParts[1];
        if ($token != $validToken) {
            echo 'Invalid token';exit;
        }

        // The last vendor ID on the previous page, relative to the requested page. When provided,
        // will get the vendors (ordered alphabetically) which come after this vendor.
        $prev = intval($this->getRequest()->getParam('prev', 0));

        // The first vendor ID on the next page, relative to the requested page. When provided,
        // will get the vendors (ordered alphabetically) which come before this vendor.
        $next = intval($this->getRequest()->getParam('next', 0));

        $vendorAttributeCode = 'manufacturer';

        $attribute = $this->eavConfig->getAttribute('catalog_product', $vendorAttributeCode);
        $options = $attribute->getSource()->getAllOptions();
        $vendors = [];
        foreach ($options as $option) {
            $value = intval($option['value']);
            if (!$value) {
                // do not return the "Not selected" option
                continue;
            }
            if ($prev !== 0 && $value <= $prev){
                //filter out "prev" pages
                continue;
            }
            if ($next !== 0 && $value >= $next){
                //filter out "next" pages
                continue;
            }
            $vendors[$value] = $option['label'];
        }
        $resultList = [];
        foreach ($vendors as $id => $name) {
            $resultList[] = [
                'id' => strval($id),
                'name' => $name,
                //'logoURL' => 'https://...'
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData($resultList);
        return $result;
    }
}