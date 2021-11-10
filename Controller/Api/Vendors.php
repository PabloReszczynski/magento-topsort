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

class Vendors extends \Magento\Framework\App\Action\Action
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

        $vendors = [
            [
                'id' => 'C0n7J6j0WySR',
                'name' => 'Cervecera Cuello Negro SpA',
                'logoURL' => 'https://www.cuellonegro.cl/wp-content/uploads/2017/05/logo_web.png',
            ],
            [
                'id' => 'y7v6kSGGUUFn',
                'name' => 'Cerveceria Chile SA',
            ],
            [
                'id' => 'vhvg6ioBj5fk',
                'name' => 'Cerveceria Coda',
            ],
            [
                'id' => 'IMwMGVfSsEpQ',
                'name' => 'Cerveceria Kunstmann Limitada',
            ],
            [
                'id' => 'zo8UXchnFWZu',
                'name' => 'Cervecerias Unidas SA',
            ],
            [
                'id' => '9SiwYqqL8vdG',
                'name' => 'Huyghe Brewery',
                'logoURL' => 'https://media-exp1.licdn.com/dms/image/C4E0BAQFE4e-wq7TlCw/company-logo_200_200/0/1519861983671?e=2159024400&v=beta&t=ZoishDxg4-kKH9fJBXRkBc_N0adqUpBmGqdB1TM5sYg',
            ]
        ];

        $result->setData($vendors);

        return $result;
    }
}