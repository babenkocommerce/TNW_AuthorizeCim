<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Controller\Jwt;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Math\Random;
use Magento\Framework\Stdlib\DateTime\DateTime;
use TNW\AuthorizeCim\Controller\Jwt\Encode;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use TNW\AuthorizeCim\Gateway\Config\Config;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

/**
 * Test Encode
 */
class EncodeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Json|MockObject
     */
    private $resultJson;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var Random|MockObject
     */
    private $mathRandom;

    /**
     * @var DateTime|MockObject
     */
    private $dataTime;

    /**
     * @var Encode
     */
    private $action;

    protected function setUp()
    {
        $resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultJson = $this->createMock(Json::class);

        $resultFactory->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->resultJson);

        $this->request = $this->createMock(RequestInterface::class);

        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isVerify3DSecure', 'getVerifyApiIdentifier', 'getVerifyOrgUnitId', 'getVerifyApiKey'])
            ->getMock();

        $this->mathRandom = $this->getMockBuilder(Random::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUniqueHash'])
            ->getMock();

        $this->dataTime = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->setMethods(['gmtTimestamp'])
            ->getMock();

        $managerHelper = new ObjectManager($this);
        $this->action = $managerHelper->getObject(Encode::class, [
            'resultFactory' => $resultFactory,
            '_request' => $this->request,
            'config' => $this->config,
            'mathRandom' => $this->mathRandom,
            'dataTime' => $this->dataTime,
        ]);
    }

    public function testExecute()
    {
        $orderDetails = [];
        $this->request->method('getParam')
            ->with('order_details')
            ->willReturn($orderDetails);

        $this->config->method('isVerify3DSecure')
            ->willReturn(true);

        $this->config->method('getVerifyApiIdentifier')
            ->willReturn('api_identifier');

        $this->config->method('getVerifyOrgUnitId')
            ->willReturn('org_unit_id');

        $this->config->method('getVerifyApiKey')
            ->willReturn('api_key');

        $uniqid = 'wqewqew';
        $this->mathRandom->method('getUniqueHash')
            ->willReturn($uniqid);

        $currentTime = 1530524567;
        $this->dataTime->method('gmtTimestamp')
            ->willReturn($currentTime);

        $jwt = (string)(new Builder())
            ->setIssuer($this->config->getVerifyApiIdentifier())
            ->setId($uniqid, true)
            ->setIssuedAt($currentTime)
            ->setExpiration($currentTime + 3600)
            ->set('OrgUnitId', $this->config->getVerifyOrgUnitId())
            ->set('Payload', ['OrderDetails' => $orderDetails])
            ->set('ObjectifyPayload', true)
            ->sign(new Sha256(), $this->config->getVerifyApiKey())
            ->getToken();

        $this->resultJson
            ->method('setData')
            ->with([
                'jwt' => $jwt
            ])
            ->willReturnSelf();

        $this->action->execute();
    }

    public function testExecuteDisable3D()
    {
        $orderDetails = [];
        $this->request->method('getParam')
            ->with('order_details')
            ->willReturn($orderDetails);

        $this->config->method('isVerify3DSecure')
            ->willReturn(false);

        $this->resultJson
            ->method('setData')
            ->with([
                'jwt' => ''
            ])
            ->willReturnSelf();

        $this->action->execute();
    }
}
