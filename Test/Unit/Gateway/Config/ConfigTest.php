<?php
/**
 *
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Config;

use TNW\AuthorizeCim\Gateway\Config\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Test Config
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    const METHOD_CODE = 'tnw_authorize_cim';

    /**
     * @var Config
     */
    private $model;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $this->model = new Config(
            $this->scopeConfigMock,
            self::METHOD_CODE
        );
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Config\Config::isActive
     */
    public function testIsActive()
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath(Config::ACTIVE), ScopeInterface::SCOPE_STORE, null)
            ->willReturn(1);

        static::assertEquals(true, $this->model->isActive());
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Config\Config::getApiLoginId
     */
    public function testGetApiLoginId()
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath(Config::LOGIN), ScopeInterface::SCOPE_STORE, null)
            ->willReturn('login_id');

        static::assertEquals('login_id', $this->model->getApiLoginId());
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Config\Config::getTransactionKey
     */
    public function testGetTransactionKey()
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath(Config::TRANSACTION_KEY), ScopeInterface::SCOPE_STORE, null)
            ->willReturn('transaction_key');

        static::assertEquals('transaction_key', $this->model->getTransactionKey());
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Config\Config::getClientKey
     */
    public function testGetClientKey()
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath(Config::CLIENT_KEY), ScopeInterface::SCOPE_STORE, null)
            ->willReturn('client_key');

        static::assertEquals('client_key', $this->model->getClientKey());
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Config\Config::getEnvironment
     */
    public function testGetEnvironment()
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath(Config::ENVIRONMENT), ScopeInterface::SCOPE_STORE, null)
            ->willReturn('sandbox');

        static::assertEquals('sandbox', $this->model->getEnvironment());
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Config\Config::isSandboxMode
     */
    public function testIsSandboxMode()
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath(Config::ENVIRONMENT), ScopeInterface::SCOPE_STORE, null)
            ->willReturn('sandbox');

        static::assertEquals(true, $this->model->isSandboxMode());
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Config\Config::getSdkUrl
     */
    public function testGetSdkUrlTest()
    {
        $this->scopeConfigMock->expects(static::at(0))
            ->method('getValue')
            ->with($this->getPath(Config::ENVIRONMENT), ScopeInterface::SCOPE_STORE, null)
            ->willReturn('sandbox');

        $this->scopeConfigMock->expects(static::at(1))
            ->method('getValue')
            ->with($this->getPath(Config::SDK_URL_TEST), ScopeInterface::SCOPE_STORE, null)
            ->willReturn('sdk_url_test');

        static::assertEquals('sdk_url_test', $this->model->getSdkUrl());
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Config\Config::getSdkUrl
     */
    public function testGetSdkUrl()
    {
        $this->scopeConfigMock->expects(static::at(0))
            ->method('getValue')
            ->with($this->getPath(Config::ENVIRONMENT), ScopeInterface::SCOPE_STORE, null)
            ->willReturn('live');

        $this->scopeConfigMock->expects(static::at(1))
            ->method('getValue')
            ->with($this->getPath(Config::SDK_URL), ScopeInterface::SCOPE_STORE, null)
            ->willReturn('sdk_url');

        static::assertEquals('sdk_url', $this->model->getSdkUrl());
    }

    /**
     * @param string $value
     * @param array $expected
     * @dataProvider getAvailableCardTypesDataProvider
     * @covers \TNW\AuthorizeCim\Gateway\Config\Config::getAvailableCardTypes
     */
    public function testGetAvailableCardTypes($value, $expected)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with($this->getPath(Config::CC_TYPES), ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        static::assertEquals(
            $expected,
            $this->model->getAvailableCardTypes()
        );
    }

    /**
     * @return array
     */
    public function getAvailableCardTypesDataProvider()
    {
        return [
            [
                'AE,VI,MC,DI,JCB',
                ['AE', 'VI', 'MC', 'DI', 'JCB']
            ],
            [
                '',
                []
            ]
        ];
    }

    /**
     * @param string $value
     * @param array $expected
     * @dataProvider getCcTypesMapperDataProvider
     */
    public function testGetCcTypesMapper($value, $expected)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with($this->getPath(Config::CC_TYPES_MAPPER), ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        static::assertEquals(
            $expected,
            $this->model->getCctypesMapper()
        );
    }

    /**
     * @return array
     */
    public function getCcTypesMapperDataProvider()
    {
        return [
            [
                '{"visa":"VI","american-express":"AE"}',
                ['visa' => 'VI', 'american-express' => 'AE']
            ],
            [
                '{invalid json}',
                []
            ],
            [
                '',
                []
            ]
        ];
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Config\Config::isCcvEnabled
     */
    public function testUseCcv()
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath(Config::USE_CCV), ScopeInterface::SCOPE_STORE, null)
            ->willReturn(1);

        static::assertEquals(true, $this->model->isCcvEnabled());
    }

    /**
     * Return config path
     *
     * @param string $field
     * @return string
     */
    private function getPath($field)
    {
        return sprintf(Config::DEFAULT_PATH_PATTERN, self::METHOD_CODE, $field);
    }
}
