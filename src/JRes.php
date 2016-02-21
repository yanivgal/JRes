<?php

namespace yanivgal;

/**
 * Class JRes
 *
 * This class represents a JSON response message.
 * The structure of the message is implemented per OmniTI Labs describing the
 * JSend specs.
 * Reference: http://labs.omniti.com/labs/jsend
 */
class JRes
{
    const INDEX_STATUS = 'status';
    const INDEX_DATA = 'data';
    const INDEX_MESSAGE = 'message';
    const INDEX_CODE = 'code';

    const STATUS_SUCCESS = 'success';
    const STATUS_FAIL = 'fail';
    const STATUS_ERROR = 'error';

    /**
     * @var array
     */
    private $responseMessage;

    /**
     * @var string
     */
    private $status;

    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $message;

    /**
     * @var int
     */
    private $errorCode;

    /**
     * @var string
     */
    private $jsonpCallback;

    /**
     * @param JRes::STATUS_SUCCESS|JRes::STATUS_FAIL|JRes::STATUS_ERROR $status
     * @param array $data
     * @param string $message
     * @param string $jsonpCallback
     */
    public function __construct(
        $status,
        array $data = null,
        $message = null,
        $jsonpCallback = null
    ) {
        $this->status = $status;
        $this->data = $data;
        $this->message = $message;
        $this->jsonpCallback = $jsonpCallback;
    }

    /**
     * @param JRes::STATUS_SUCCESS|JRes::STATUS_FAIL|JRes::STATUS_ERROR $status
     * @return JRes
     */
    public function status($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param array $data
     * @return JRes
     */
    public function data(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return JRes
     */
    public function addData($key, $value)
    {
        if (isset($key)) {
            $this->data[$key] = $value;
        }
        return $this;
    }

    /**
     * @param string $message
     * @return JRes
     */
    public function message($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param string $jsonpCallback JSONP callback name
     * @return JRes
     */
    public function jsonpCallback($jsonpCallback)
    {
        $this->jsonpCallback = $jsonpCallback;
        return $this;
    }

    /**
     * @return string JSON Response message
     */
    public function toJson()
    {
        switch ($this->status) {
            case self::STATUS_SUCCESS:
                return $this->buildResponseMessage([
                    self::INDEX_STATUS,
                    self::INDEX_DATA
                ]);
            case self::STATUS_FAIL:
                return $this->buildResponseMessage([
                    self::INDEX_STATUS,
                    self::INDEX_MESSAGE
                ]);
            case self::STATUS_ERROR:
                return $this->buildResponseMessage([
                    self::INDEX_STATUS,
                    self::INDEX_MESSAGE
                ]);
            default:
                return $this->buildInternalErrorResponseMessage(
                    "Status index is missing from built response message"
                );
        }
    }

    /**
     * @return string JSONP Rsponse message
     */
    public function toJsonp()
    {
        $responseMessage = $this->toJson();
        if (isset($this->jsonpCallback)) {
            $responseMessage = $this->jsonpCallback . '(' . $responseMessage . ')';
        }
        return $responseMessage;
    }

    /**
     * @param array $requiredIndexes
     * @return string Response message
     */
    private function buildResponseMessage(array $requiredIndexes)
    {
        if (isset($this->status)) {
            $this->responseMessage[self::INDEX_STATUS] = $this->status;
        } else {
            return $this->buildInternalErrorResponseMessage(
                "Status index is missing from built response message"
            );
        }

        if (isset($this->message)) {
            $this->responseMessage[self::INDEX_MESSAGE] = $this->message;
        } elseif (in_array(self::INDEX_MESSAGE, $requiredIndexes)) {
            return $this->buildInternalErrorResponseMessage(
                "Message index is missing from built response message"
            );
        }

        if (isset($this->data)) {
            $this->responseMessage[self::INDEX_DATA] = $this->data;
        } elseif (in_array(self::INDEX_DATA, $requiredIndexes)) {
            return $this->buildInternalErrorResponseMessage(
                "Data index is missing from built response message"
            );
        }

        if (isset($this->errorCode)) {
            $this->responseMessage[self::INDEX_CODE] = $this->errorCode;
        } elseif (in_array(self::INDEX_CODE, $requiredIndexes)) {
            return $this->buildInternalErrorResponseMessage(
                "Code index is missing from built response message"
            );
        }

        return json_encode($this->responseMessage);
    }

    /**
     * @param string $message Internal error message
     * @return string Response message
     */
    private function buildInternalErrorResponseMessage($message)
    {
        $this->responseMessage[self::INDEX_STATUS] = self::STATUS_ERROR;
        $this->responseMessage[self::INDEX_MESSAGE] = $message;
        return json_encode($this->responseMessage);
    }
}