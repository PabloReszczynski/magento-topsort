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

use Magento\Framework\Controller\ResultFactory;

class Categories extends \Magento\Framework\App\Action\Action
{

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

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);


        $categories = [
            [
                'id' => 'ahEDqV5uhjj8',
                'name' => 'Cervezas/Ales/Triple Ale',
            ],
            [
                'id' => 'cJfoUUzG6GOy',
                'name' => 'Cervezas/Ales/Amber',
            ],
            [
                'id' => 'oTcnv0fJCRiL',
                'name' => 'Cervezas/Lagers/Bocks',
            ],
            [
                'id' => 'JspphvZBzV09',
                'name' => 'Cervezas/Lagers/Rubias',
            ],
        ];

        $result->setData($categories);

        return $result;
    }
}