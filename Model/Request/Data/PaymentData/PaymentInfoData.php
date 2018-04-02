<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Request\Data\PaymentData;

use Magento\Framework\DataObject;

/**
 * Contain payment information
 */
class PaymentInfoData extends DataObject
{
    /** Credit card type field key */
    const PAYMENT_INFO_TYPE_KEY = 'info_type';

    /** Credit card last 4 number field key */
    const PAYMENT_INFO_LAST4_KEY = 'info_last4';

    /** Credit card expiration month field key */
    const PAYMENT_INFO_EXP_MONTH_KEY = 'info_exp_month';

    /** Credit card expiration year field key */
    const PAYMENT_INFO_EXP_YEAR_KEY = 'info_exp_year';

    /** Credit card number field key */
    const PAYMENT_INFO_CARD_NUMBER = 'info_card_number';

    /** Credit card code */
    const PAYMENT_INFO_CARD_CODE = 'info_card_code';

    /**
     * Set credit card type
     *
     * @param string $ccType
     * @return $this
     */
    public function setCcType($ccType)
    {
        $this->setData(self::PAYMENT_INFO_TYPE_KEY, $ccType);

        return $this;
    }

    /**
     * Return credit card type
     *
     * @return string|null
     */
    public function getCcType()
    {
        return $this->getData(self::PAYMENT_INFO_TYPE_KEY);
    }

    /**
     * Set credit card last 4 number
     *
     * @param string $ccLast4
     * @return $this
     */
    public function setLast4($ccLast4)
    {
        $this->setData(self::PAYMENT_INFO_LAST4_KEY, $ccLast4);

        return $this;
    }

    /**
     * Return credit card last 4 number
     *
     * @return string|null
     */
    public function getLast4()
    {
        return $this->getData(self::PAYMENT_INFO_LAST4_KEY);
    }

    /**
     * Set credit card expiration month
     *
     * @param string $expMonth
     * @return $this
     */
    public function setExpMonth($expMonth)
    {
        $this->setData(self::PAYMENT_INFO_EXP_MONTH_KEY, $expMonth);

        return $this;
    }

    /**
     * Return credit card expiration month
     *
     * @return string|null
     */
    public function getExpMonth()
    {
        return $this->getData(self::PAYMENT_INFO_EXP_MONTH_KEY);
    }

    /**
     * Set credit card expiration year
     *
     * @param string $expYear
     * @return $this
     */
    public function setExpYear($expYear)
    {
        $this->setData(self::PAYMENT_INFO_EXP_YEAR_KEY, $expYear);

        return $this;
    }

    /**
     * Return credit card expiration year
     *
     * @return string|null
     */
    public function getExpYear()
    {
        return $this->getData(self::PAYMENT_INFO_EXP_YEAR_KEY);
    }

    /**
     * Set credit card number
     *
     * @param string $cardNumber
     * @return $this
     */
    public function setCardNumber($cardNumber)
    {
        $this->setData(self::PAYMENT_INFO_CARD_NUMBER, $cardNumber);

        return $this;
    }

    /**
     * Return credit card number
     *
     * @return string|null
     */
    public function getCardNumber()
    {
        return $this->getData(self::PAYMENT_INFO_CARD_NUMBER);
    }

    /**
     * Set credit card code
     *
     * @param string $cardCode
     * @return $this
     */
    public function setCardCode($cardCode)
    {
        $this->setData(self::PAYMENT_INFO_CARD_CODE, $cardCode);

        return $this;
    }

    /**
     * Return credit card code
     *
     * @return string|null
     */
    public function getCardCode()
    {
        return $this->getData(self::PAYMENT_INFO_CARD_CODE);
    }
}
