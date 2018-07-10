<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Request;

use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Gateway\Request\AddressDataBuilder;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test AddressDataBuilder
 */
class AddressDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDO;

    /**
     * @var OrderAdapterInterface|MockObject
     */
    private $order;

    /**
     * @var AddressDataBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $this->order = $this->createMock(OrderAdapterInterface::class);

        $this->builder = new AddressDataBuilder(new SubjectReader());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuildReadPaymentException()
    {
        $buildSubject = [
            'payment' => null,
        ];

        $this->builder->build($buildSubject);
    }

    public function testBuildNoAddresses()
    {
        $this->paymentDO->method('getOrder')
            ->willReturn($this->order);

        $this->order->method('getBillingAddress')
            ->willReturn(null);

        $this->order->method('getShippingAddress')
            ->willReturn(null);

        $buildSubject = [
            'payment' => $this->paymentDO,
        ];

        self::assertEquals([], $this->builder->build($buildSubject));
    }

    /**
     * @param array $addressData
     * @param array $expectedResult
     *
     * @dataProvider dataProviderBuild
     */
    public function testBuild($addressData, $expectedResult)
    {
        $address = $this->getAddressMock($addressData);

        $this->paymentDO->method('getOrder')
            ->willReturn($this->order);

        $this->order->method('getBillingAddress')
            ->willReturn($address);

        $this->order->method('getShippingAddress')
            ->willReturn($address);

        $buildSubject = [
            'payment' => $this->paymentDO,
        ];

        self::assertEquals($expectedResult, $this->builder->build($buildSubject));
    }

    /**
     * @return array
     */
    public function dataProviderBuild()
    {
        return [
            [
                [
                    'first_name' => 'John',
                    'last_name' => 'Smith',
                    'email' => 'j.smith@magento.com',
                    'company' => 'Company Ltd.',
                    'phone' => '987654321',
                    'street_1' => 'street1',
                    'city' => 'Chicago',
                    'region_code' => 'IL',
                    'country_id' => 'US',
                    'post_code' => '00000'
                ],
                [
                    'transaction_request' => [
                        'bill_to' => [
                            'first_name' => 'John',
                            'last_name' => 'Smith',
                            'company' => 'Company Ltd.',
                            'address' => 'street1',
                            'city' => 'Chicago',
                            'state' => 'IL',
                            'zip' => '00000',
                            'country' => 'US',
                            'phone_number' => '987654321',
                            'email' => 'j.smith@magento.com',
                        ],
                        'ship_to' => [
                            'first_name' => 'John',
                            'last_name' => 'Smith',
                            'company' => 'Company Ltd.',
                            'address' => 'street1',
                            'city' => 'Chicago',
                            'state' => 'IL',
                            'zip' => '00000',
                            'country' => 'US',
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * @param array $addressData
     * @return AddressAdapterInterface|MockObject
     */
    private function getAddressMock($addressData)
    {
        $addressMock = $this->createMock(AddressAdapterInterface::class);

        $addressMock->expects(self::exactly(2))
            ->method('getFirstname')
            ->willReturn($addressData['first_name']);
        $addressMock->expects(self::exactly(2))
            ->method('getLastname')
            ->willReturn($addressData['last_name']);
        $addressMock->expects(self::exactly(1))
            ->method('getEmail')
            ->willReturn($addressData['email']);
        $addressMock->expects(self::exactly(2))
            ->method('getCompany')
            ->willReturn($addressData['company']);
        $addressMock->expects(self::exactly(1))
            ->method('getTelephone')
            ->willReturn($addressData['phone']);
        $addressMock->expects(self::exactly(2))
            ->method('getStreetLine1')
            ->willReturn($addressData['street_1']);
        $addressMock->expects(self::exactly(2))
            ->method('getCity')
            ->willReturn($addressData['city']);
        $addressMock->expects(self::exactly(2))
            ->method('getRegionCode')
            ->willReturn($addressData['region_code']);
        $addressMock->expects(self::exactly(2))
            ->method('getPostcode')
            ->willReturn($addressData['post_code']);
        $addressMock->expects(self::exactly(2))
            ->method('getCountryId')
            ->willReturn($addressData['country_id']);

        return $addressMock;
    }
}
