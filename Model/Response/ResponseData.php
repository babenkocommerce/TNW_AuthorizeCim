<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Model\Response;

use Magento\Framework\DataObject;

class ResponseData extends DataObject
{
    /** Success code */
    const SUCCESS_CODE = 'I00001';

    /** The record cannot be found */
    const RECORD_NOT_FOUND_CODE = 'E00040';

    /** Response code from response */
    const RESPONSE_CODE_KEY = 'response_code';

    /** Response text from response */
    const RESPONSE_TEXT_KEY = 'response_text';

    /** Requested data from response */
    const RESPONSE_DATA_KEY = 'requested_data';

    /**
     * Create result by response
     *
     * @param string|null $curlResult
     * @return $this
     * @throws \Exception
     */
    public function createResult($curlResult)
    {
        if (!$curlResult) {
            throw new \Exception(__('Response from authorize.net is empty'));
        }

        $curlEncode = json_decode($curlResult, true);

        if (isset($curlEncode['messages'])) {
            $message = $curlEncode['messages']['message'][0];
            $this->setData(self::RESPONSE_CODE_KEY, $message['code']);
            $this->setData(self::RESPONSE_TEXT_KEY, $message['text']);
            unset($curlEncode['messages']);
        }

        $this->setData(self::RESPONSE_DATA_KEY, $curlEncode);

        return $this;
    }

    /**
     * Return response code
     *
     * @return string
     */
    public function getResponseCode()
    {
        return $this->getData(self::RESPONSE_CODE_KEY);
    }

    /**
     * Return response text
     *
     * @return string
     */
    public function getResponseText()
    {
        return $this->getData(self::RESPONSE_TEXT_KEY);
    }

    /**
     * Return requested data
     *
     * @return array
     */
    public function getResponseData()
    {
        return $this->getData(self::RESPONSE_DATA_KEY);
    }
}
