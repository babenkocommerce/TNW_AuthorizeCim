<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Validator;

use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Gateway\Validator\TransactionResponseValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * TransactionResponseValidator Test
 * @covers \TNW\AuthorizeCim\Gateway\Validator\TransactionResponseValidator
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
     * @covers \TNW\AuthorizeCim\Gateway\Validator\TransactionResponseValidator::validate()
     * @param array $validationSubject
     * @param $isValid
     * @param $messages
     *
     * @dataProvider dataProviderTestValidate
     */
    public function testValidate(array $validationSubject, $isValid, $messages)
    {
        $this->resultInterfaceFactory->method('create')
            ->with([
                'isValid' => (bool)$isValid,
                'failsDescription' => $messages
            ]);

        $this->validator->validate($validationSubject);
    }

    /**
     * @return array
     */
    public function dataProviderTestValidate()
    {
        $transaction = new \net\authorize\api\contract\v1\TransactionResponseType;
        $transaction->setMessages([]);

        $messages = new \net\authorize\api\contract\v1\MessagesType;
        $messages->setResultCode('Ok');

        $objectSuccess = new \net\authorize\api\contract\v1\CreateTransactionResponse;
        $objectSuccess->setTransactionResponse($transaction);
        $objectSuccess->setMessages($messages);

        $messageAType = new \net\authorize\api\contract\v1\TransactionResponseType\MessagesAType\MessageAType;
        $messageAType->setCode(500);
        $messageAType->setDescription('Test error Message');

        $messageSuccess = new \net\authorize\api\contract\v1\TransactionResponseType\MessagesAType\MessageAType;
        $messageSuccess->setCode(1);
        $messageSuccess->setDescription('Success');

        $transaction = new \net\authorize\api\contract\v1\TransactionResponseType;
        $transaction->setMessages([$messageAType, $messageSuccess]);

        $messages = new \net\authorize\api\contract\v1\MessagesType;
        $messages->setResultCode('Ok');
        $messages->setMessage([]);

        $objectError = new \net\authorize\api\contract\v1\CreateTransactionResponse;
        $objectError->setTransactionResponse($transaction);
        $objectError->setMessages($messages);

        return [
            [
                ['response' => ['object' => $objectSuccess]],
                true,
                []
            ],
            [
                ['response' => ['object' => $objectError]],
                false,
                [
                    __('Test error Message')
                ]
            ],
        ];
    }
}
