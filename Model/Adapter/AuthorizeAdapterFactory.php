<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TNW\AuthorizeCim\Model\Adapter;

use TNW\AuthorizeCim\Gateway\Config\Config;
use Magento\Framework\ObjectManagerInterface;

/**
 * This factory is preferable to use for Authorize adapter instance creation.
 */
class AuthorizeAdapterFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     */
    public function __construct(ObjectManagerInterface $objectManager, Config $config)
    {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * Creates instance of Authorize Adapter.
     *
     * @param int $storeId if null is provided as an argument, then current scope will be resolved
     * by \Magento\Framework\App\Config\ScopeCodeResolver (useful for most cases) but for adminhtml area the store
     * should be provided as the argument for correct config settings loading.
     * @return AuthorizeAdapter
     */
    public function create($storeId = null)
    {
        return $this->objectManager->create(
            AuthorizeAdapter::class,
            [
                'apiLoginId' => $this->config->getApiLoginId($storeId),
                'transactionKey' => $this->config->getTransactionKey($storeId),
                'sandboxMode' => $this->config->isSandboxMode($storeId)
            ]
        );
    }
}
