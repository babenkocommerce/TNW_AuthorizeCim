<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Validator;

use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;
use TNW\AuthorizeCim\Gateway\Validator\GeneralResponseValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * GeneralResponseValidator Test
 * @covers \TNW\AuthorizeCim\Gateway\Validator\GeneralResponseValidator
 */
class GeneralResponseValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResultInterfaceFactory|MockObject
     */
    private $resultInterfaceFactory;

    /**
     * @var GeneralResponseValidator
     */
    private $validator;

    protected function setUp()
    {
        $this->resultInterfaceFactory = $this->getMockBuilder(ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->validator = new GeneralResponseValidator(
            $this->resultInterfaceFactory,
            new SubjectReader()
        );
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Validator\GeneralResponseValidator::validate()
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

        $transaction = new \net\authorize\api\contract\v1\TransactionResponseType;
        $transaction->setMessages([]);

        $messageAType = new \net\authorize\api\contract\v1\MessagesType\MessageAType;
        $messageAType->setCode(500);
        $messageAType->setText('Test error Message');

        $messages = new \net\authorize\api\contract\v1\MessagesType;
        $messages->setResultCode('Error');
        $messages->setMessage([$messageAType]);

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
                    __('%1: %2', 500, 'Test error Message')
                ]
            ],
        ];
    }
}
