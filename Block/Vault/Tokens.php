<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Block\Vault;

use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\CcConfigProvider;
use Magento\Vault\Model\CustomerTokenManagement;
use TNW\AuthorizeCim\Model\Ui\ConfigProvider;

class Tokens extends \Magento\Framework\View\Element\Template
{
    /** @var CustomerTokenManagement */
    protected $customerTokenManagement;

    /** @var CcConfigProvider */
    protected $configProvider;

    /** @var array */
    protected $icons;

    public function __construct(
        Template\Context $context,
        CustomerTokenManagement $customerTokenManagement,
        CcConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerTokenManagement = $customerTokenManagement;
        $this->configProvider = $configProvider;
    }

    /** @return \Magento\Vault\Api\Data\PaymentTokenInterface[] */
    public function getCustomerTokens()
    {
        $tokens = $this->customerTokenManagement->getCustomerSessionTokens();
        $methodTokens = [];

        foreach ($tokens as $token) {
            if ($token->getPaymentMethodCode() === ConfigProvider::CODE) {
                $methodTokens[] = $token;
            }
        }

        return $methodTokens;
    }

    /**
     * @param string $code
     * @return false|array
     */
    public function getIcon($code)
    {
        if (isset($this->getIcons()[$code])) {
            return $this->getIcons()[$code];
        }

        return false;
    }

    /** @return array */
    protected function getIcons()
    {
        if ($this->icons) {
            return $this->icons;
        }

        $this->icons = $this->configProvider->getIcons();

        return $this->icons;
    }
}
