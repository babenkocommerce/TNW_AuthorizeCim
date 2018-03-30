<?php
/**
 * TNW_AuthorizeCim extension
 * NOTICE OF LICENSE
 *
 * This source file is subject to the OSL 3.0 License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 *
 * @category  Pmclain
 * @package   TNW_AuthorizeCim
 * @copyright Copyright (c) 2017-2018
 * @license   Open Software License (OSL 3.0)
 */

namespace TNW\AuthorizeCim\Gateway\Http\Client;

use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;
use Magento\Framework\DataObjectFactory;

abstract class AbstractTransaction implements ClientInterface
{
    /** @var LoggerInterface */
    protected $_logger;

    /** @var Logger */
    protected $_customLogger;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @param DataObjectFactory $dataObjectFactory
     * @param LoggerInterface $logger
     * @param Logger $customLogger
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        LoggerInterface $logger,
        Logger $customLogger
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->_logger = $logger;
        $this->_customLogger = $customLogger;
    }

    /**
     * @param TransferInterface $transferObject
     * @return mixed
     * @throws ClientException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $data = $transferObject->getBody();
        $log = [
            'request' => $data,
            'client' => static::class
        ];

        $response['object'] = [];

        try {
            $response['object'] = $this->process($data);
        } catch (\Exception $e) {
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong.');
            $this->_logger->critical($message);
            throw new ClientException($message);
        } finally {
            $log['response'] = (array)$response['object'];
            $this->_customLogger->debug($log);
        }

        return $response;
    }

    /**
     * Create data object
     *
     * @param array $data
     * @return array|DataObject
     */
    protected function createDataObject($data)
    {
        $convert = false;
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->createDataObject($value);
            }
            if (!is_numeric($key)) {
                $convert = true;
            }
        }
        return $convert ? $this->dataObjectFactory->create(['data' => $data]) : $data;
    }

    abstract protected function process(array $data);
}
