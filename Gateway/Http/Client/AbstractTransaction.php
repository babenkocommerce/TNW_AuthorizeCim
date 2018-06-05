<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;
use TNW\AuthorizeCim\Gateway\Helper\DataObject;
use TNW\AuthorizeCim\Model\Adapter\AuthorizeAdapterFactory;

abstract class AbstractTransaction implements ClientInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Logger
     */
    private $customLogger;

    /**
     * @var AuthorizeAdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var DataObject
     */
    private $dataObjectHelper;

    /**
     * @param LoggerInterface $logger
     * @param Logger $customLogger
     * @param AuthorizeAdapterFactory $adapterFactory
     * @param DataObject $dataObjectHelper
     */
    public function __construct(
        LoggerInterface $logger,
        Logger $customLogger,
        AuthorizeAdapterFactory $adapterFactory,
        DataObject $dataObjectHelper
    ) {
        $this->logger = $logger;
        $this->customLogger = $customLogger;
        $this->adapterFactory = $adapterFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param TransferInterface $transferObject
     * @return array
     * @throws ClientException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $data = $transferObject->getBody();
        $log = [
            'request' => $data,
            'client' => static::class
        ];

        $response['object'] = new \stdClass();

        try {
            $response['object'] = $this->process($data);
        } catch (\Exception $e) {
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong.');
            $this->logger->critical($message);
            throw new ClientException($message);
        } finally {
            $log['response'] = [];
            $this->dataObjectHelper->populateWithObject($log['response'], $response['object']);
            $this->customLogger->debug($log);
        }

        return $response;
    }

    /**
     * @param array $data
     * @return \net\authorize\api\contract\v1\AnetApiResponseType
     */
    abstract protected function process(array $data);
}
