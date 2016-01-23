<?php

namespace Centaur\Replies;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

/**
 * A data transfer object containing the results of a manager class action
 */

abstract class Reply implements Jsonable, Arrayable
{
    /**
     * The data paylod returned by the manager class
     * @var array
     */
    protected $payload = [];

    /**
     * A message from the manager class to be conveyed to the user
     * @var string
     */
    protected $message = '';

    /**
     * The reccomended status code to include with the server response
     * @var integer
     */
    protected $statusCode = 400;

    /**
     * A boolean flag indicating if the manager class action was successful
     * @var boolean
     */
    protected $success = false;

    /**
     * If an exception was caught, it will be available here for reference
     * @var Exception|Null
     */
    protected $exception = null;

    /**
     * Occasionally we will need to manually specify a redirect location when
     * an error occurs; this is that url.
     * @var null
     */
    protected $redirectUrl = null;

    /**
     * @param string $message
     * @param array $payload
     * @param Exception $exception
     */
    public function __construct($message = "", array $payload = [], Exception $exception = null)
    {
        $this->message = $message;
        $this->payload = $payload;
        $this->exception = $exception;
    }

    /**
     * Convert the dispatch to the appropriate redirect or response object
     * @var   string $url
     * @return Response|Redirect
     */
    abstract public function dispatch($url = '/');

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->success;
    }

    /**
     * @return bool
     */
    public function isFailure()
    {
        return !$this->success;
    }

    /**
     * @return mixed
     */
    public function hasMessage()
    {
        return !empty($this->message);
    }

    /**
     * @return mixed
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function hasPayload()
    {
        return !empty($this->payload);
    }

    /**
     * @return void
     */
    public function clearPayload()
    {
        $this->payload = [];
    }

    /**
     * Determine if a value exists within this object
     * @param  string $key
     * @return void
     */
    public function has($key)
    {
        if ($key == 'message') {
            return !empty($this->message);
        }

        if ($key == 'exception') {
            return !is_null($this->exception);
        }

        return array_key_exists($key, $this->payload);
    }

    /**
     * Unset a payload array key => value
     * @param  string $key
     * @return void
     */
    public function remove($key)
    {
        if ($key == 'message') {
            $this->message = '';
        }

        if ($key == 'exception') {
            $this->exception = null;
        }

        if (array_key_exists($key, $this->payload)) {
            unset($this->payload[$key]);
        }
    }

    /**
     * @return boolean
     */
    public function caughtAnException()
    {
        return !is_null($this->exception);
    }

    /**
     * @return Exception
     */
    public function setException(Exception $e)
    {
        return $this->exception = $e;
    }

    /**
     * Set the redirect url
     * @param string $url
     */
    public function setRedirectUrl($url = null)
    {
        $this->returnUrl = $url;
    }

    /**
     * Convert the dispatch object to an array
     * @return array
     */
    public function toArray()
    {
        $dispatch = [];
        $dispatch['status'] = $this->statusCode;

        if ($this->hasMessage()) {
            $dispatch['message'] = $this->message;
        }

        if ($this->hasPayload()) {
            $dispatch = array_merge($dispatch, $this->payload);
        }

        return $dispatch;
    }

    /**
     * Convert the dispatch object to json
     * @param  integer $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Dynamically retrieve payload data
     * @param  string $key
     * @return mixed|null
     */
    public function __get($key)
    {
        if ($key == 'message') {
            return $this->message;
        }

        if ($key == 'statusCode') {
            return $this->statusCode;
        }

        if ($key == 'exception') {
            return $this->exception;
        }

        if (array_key_exists($key, $this->payload)) {
            return $this->payload[$key];
        }

        return null;
    }
}
