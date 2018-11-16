<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Model;

use TNW\AuthorizeCim\Gateway\Response\PaymentDetailsHandler;
use TNW\AuthorizeCim\Model\Ui\ConfigProvider;
use Magento\Payment\Api\PaymentVerificationInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Processes CVV codes mapping from Authorize.NET transaction to electronic merchant systems standard.
 *
 * @see http://www.emsecommerce.net/avs_cvv2_response_codes.htm
 */
class CvvEmsCodeMapper implements PaymentVerificationInterface
{
    /**
     * Default code for mismatch mapping
     *
     * @var string
     */
    private static $notProvidedCode = 'P';

    /**
     * Gets payment CVV verification code.
     *
     * @param OrderPaymentInterface $orderPayment
     * @return string
     * @throws \InvalidArgumentException If specified order payment has different payment method code.
     */
    public function getCode(OrderPaymentInterface $orderPayment)
    {
        if ($orderPayment->getMethod() !== ConfigProvider::CODE) {
            throw new \InvalidArgumentException(
                'The "' . $orderPayment->getMethod() . '" does not supported by Authorize.NET CVV mapper.'
            );
        }

        $additionalInfo = $orderPayment->getAdditionalInformation();
        if (empty($additionalInfo[PaymentDetailsHandler::CVV_CODE])) {
            return self::$notProvidedCode;
        }

        return $additionalInfo[PaymentDetailsHandler::CVV_CODE];
    }
}
