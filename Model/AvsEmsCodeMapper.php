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
 * Processes AVS codes mapping from Braintree transaction to electronic merchant systems standard.
 *
 * @see http://www.emsecommerce.net/avs_cvv2_response_codes.htm
 */
class AvsEmsCodeMapper implements PaymentVerificationInterface
{
    /**
     * Default code for mismatching mapping.
     *
     * @var string
     */
    private static $unavailableCode = '';

    /**
     * Gets payment AVS verification code.
     *
     * @param OrderPaymentInterface $orderPayment
     * @return string
     * @throws \InvalidArgumentException If specified order payment has different payment method code.
     */
    public function getCode(OrderPaymentInterface $orderPayment)
    {
        if ($orderPayment->getMethod() !== ConfigProvider::CODE) {
            throw new \InvalidArgumentException(
                'The "' . $orderPayment->getMethod() . '" does not supported by Authorize.NET AVS mapper.'
            );
        }

        $additionalInfo = $orderPayment->getAdditionalInformation();
        if (empty($additionalInfo[PaymentDetailsHandler::AVS_CODE])) {
            return self::$unavailableCode;
        }

        return $additionalInfo[PaymentDetailsHandler::AVS_CODE];
    }
}
