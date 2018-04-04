<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Model\Adapter;

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use TNW\AuthorizeCim\Gateway\Helper\DataObject;

class AuthorizeAdapter
{
    /**
     * @var string
     */
    private $apiLoginId;

    /**
     * @var string
     */
    private $transactionKey;

    /**
     * @var string
     */
    private $environment;

    /**
     * @var DataObject
     */
    private $dataObjectHelper;

    /**
     * AuthorizeAdapter constructor.
     * @param string $apiLoginId
     * @param string $transactionKey
     * @param string $environment
     * @param DataObject $dataObjectHelper
     */
    public function __construct(
        $apiLoginId,
        $transactionKey,
        $environment,
        DataObject $dataObjectHelper
    ) {
        $this->apiLoginId = $apiLoginId;
        $this->transactionKey = $transactionKey;
        $this->environment = $environment;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param array $attributes
     * @return AnetAPI\AnetApiResponseType
     */
    public function transaction(array $attributes)
    {
        $transactionRequest = new AnetAPI\CreateTransactionRequest();
        $this->dataObjectHelper->populateWithArray(
            $transactionRequest,
            array_merge($attributes, [
                'transaction_request' => ['transaction_type' => 'authCaptureTransaction'],
                'merchant_authentication' => ['name' => $this->apiLoginId, 'transaction_key' => $this->transactionKey]
            ])
        );

        $controller = new AnetController\CreateTransactionController($transactionRequest);
        return $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
    }
}
