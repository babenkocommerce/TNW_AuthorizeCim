<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Request;

use Magento\Framework\Exception\PaymentException;
use TNW\AuthorizeCim\Gateway\Config\Config;
use TNW\AuthorizeCim\Model\Response\ResponseData;

class RequestAbstract
{
    /** Merchant authentication field key */
    const MERCHANT_AUTH_FIELD = 'merchantAuthentication';

    /** Merchant unique API login ID */
    const NAME_FIELD = 'name';

    /** Merchant unique API transaction key */
    const TRANSACTION_KEY_FIELD = 'transactionKey';

    /** request type */
    const REQUEST_TYPE = 'POST';

    /**
     * @var Config
     */
    private $config;
    /**
     * @var ResponseData
     */
    private $responseData;

    /**
     * @param ResponseData $responseData
     * @param Config $config
     */
    public function __construct(
        ResponseData $responseData,
        Config $config
    ) {
        $this->config = $config;
        $this->responseData = $responseData;
    }

    /**
     * Create request to Authorize.net server
     *
     * @param array $request
     * @return ResponseData
     */
    protected function postRequest(array $request)
    {
        $content = json_encode($request);
        $curl = curl_init($this->getUrl());
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, self::REQUEST_TYPE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json;',
            'Accept: application/json',
            'Content-Length: ' . strlen($content),
        ]);
        $curlResult = $this->removeUtf8Bom(curl_exec($curl));
        curl_close($curl);

        return $this->responseData->createResult($curlResult);
    }

    /**
     * Return merchant authentication information
     *
     * @return array
     */
    protected function getMerchantAuthentication()
    {
        return [
            self::MERCHANT_AUTH_FIELD => [
                self::NAME_FIELD => $this->getApiLoginId(),
                self::TRANSACTION_KEY_FIELD => $this->getTransactionKey()
            ]
        ];
    }

    /**
     * Return API login ID
     *
     * @return string
     * @throws PaymentException
     */
    protected function getApiLoginId()
    {
        $result = trim($this->config->getApiLoginId());

        if (!$result) {
            throw new PaymentException(__('API login ID is not set'));
        }

        return $result;
    }

    /**
     * Return API transaction key
     *
     * @return string
     * @throws PaymentException
     */
    protected function getTransactionKey()
    {
        $result = trim($this->config->getTransactionKey());

        if (!$result) {
            throw new PaymentException(__('API transaction key is not set'));
        }

        return $result;
    }

    /**
     * Return client key
     *TODO Проверить может метод здесь и не нужен
     * @return string
     * @throws PaymentException
     */
    protected function getClientKey()
    {
        $result = trim($this->config->getClientKey());

        if (!$result) {
            throw new PaymentException(__('API client key is not set'));
        }

        return $result;
    }

    /**
     * Return URL for request
     *
     * @return string
     */
    private function getUrl()
    {
        //TODO Сделать через конфиг
//        const URL_SANDBOX = 'https://apitest.authorize.net/xml/v1/request.api';
//        const URL_PRODUCTION = 'https://api.authorize.net/xml/v1/request.api';
        return 'https://apitest.authorize.net/xml/v1/request.api';
    }

    /**
     * Remove UTF-8 Bom char
     *
     * @param $string
     * @return string
     */
    private function removeUtf8Bom($string)
    {
        $bom = pack('H*', 'EFBBBF');
        $text = preg_replace("/^$bom/", '', $string);

        return $text;
    }
}
