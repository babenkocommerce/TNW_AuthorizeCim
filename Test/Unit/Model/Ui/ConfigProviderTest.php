<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Model\Ui;

use Magento\Framework\UrlInterface;
use TNW\AuthorizeCim\Gateway\Config\Config;
use TNW\AuthorizeCim\Model\Ui\ConfigProvider;
use Magento\Customer\Model\Session;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * ConfigProvider Test
 */
class ConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var Session|MockObject
     */
    private $session;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilder;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    protected function setUp()
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId'])
            ->getMock();

        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configProvider = new ConfigProvider(
            $this->config,
            $this->session,
            $this->urlBuilder
        );
    }

    /**
     * Run test getConfig method
     *
     * @covers ConfigProvider::getConfig()
     */
    public function testGetConfig()
    {
        $this->session->method('getStoreId')
            ->willReturn(5);

        $this->config->method('isActive')
            ->with(5)
            ->willReturn(true);

        $this->config->method('getClientKey')
            ->with(5)
            ->willReturn('client_key');

        $this->config->method('getApiLoginId')
            ->with(5)
            ->willReturn('api_login_id');

        $this->config->method('getSdkUrl')
            ->with(5)
            ->willReturn('sdk_url');

        $this->config->method('isVerify3DSecure')
            ->with(5)
            ->willReturn(true);

        $this->config->method('getThresholdAmount')
            ->with(5)
            ->willReturn(34);

        $this->config->method('get3DSecureSpecificCountries')
            ->with(5)
            ->willReturn(['test']);

        $this->config->method('getVerifySdkUrl')
            ->with(5)
            ->willReturn('verify_sdk_url');

        $this->urlBuilder->method('getUrl')
            ->with('tnw_authorizecim/jwt/encode')
            ->willReturn('encode_url');

        $expected = [
            'payment' => [
                ConfigProvider::CODE => [
                    'isActive' => true,
                    'clientKey' => 'client_key',
                    'apiLoginID' => 'api_login_id',
                    'sdkUrl' => 'sdk_url',
                    'vaultCode' => ConfigProvider::VAULT_CODE,
                ],
                'verify_authorize' => [
                    'enabled' => (int)true,
                    'thresholdAmount' => 34,
                    'specificCountries' => ['test'],
                    'sdkUrl' => 'verify_sdk_url',
                    'jwtUrl' => 'encode_url',
                ],
            ]
        ];

        self::assertEquals($expected, $this->configProvider->getConfig());
    }
}
