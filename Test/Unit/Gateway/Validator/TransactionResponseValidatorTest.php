<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Validator;

use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Gateway\Validator\TransactionResponseValidator;
use Magento\Framework\Phrase;
use Magento\Payment\Gateway\Validator\Result;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * TransactionResponseValidator Test
 */
class TransactionResponseValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResultInterfaceFactory|MockObject
     */
    private $resultInterfaceFactory;

    /**
     * @var TransactionResponseValidator
     */
    private $validator;

    protected function setUp()
    {
        $this->resultInterfaceFactory = $this->getMockBuilder(ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->validator = new TransactionResponseValidator(
            $this->resultInterfaceFactory,
            new SubjectReader()
        );
    }

    /**
     * @covers TransactionResponseValidator::validate()
     * @param array $validationSubject
     * @param $isValid
     * @param $messages
     *
     * @dataProvider dataProviderTestValidate
     */
    public function testValidate(array $validationSubject, $isValid, $messages)
    {
        /** @var ResultInterface|MockObject $result */
        $result = new Result($isValid, $messages);

        $this->resultInterfaceFactory->method('create')
            ->willReturn($result);

        self::assertEquals($result, $this->validator->validate($validationSubject));
    }

    /**
     * @return array
     */
    public function dataProviderTestValidate()
    {
        $transaction = new \net\authorize\api\contract\v1\TransactionResponseType;
        $transaction->setMessages([]);

        $messages = new \net\authorize\api\contract\v1\MessagesType;
        $messages->setMessage([]);

        $object = new \net\authorize\api\contract\v1\CreateTransactionResponse;
        $object->setTransactionResponse($transaction);
        $object->setMessages($messages);

        return [
            [
                ['response' => ['object' => $object]],
                true,
                []
            ]
        ];
    }
}