<?php

namespace App\Classes\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class InternalAppResponse
{
    /**
     * Hold the state of the response which can either be `success` or `error`
     *
     * @var string
     */
    public $status;

    /**
     * Hold the status code of the response
     *
     * @var integer
     */
    public $statusCode;

    /**
     * Hold the message of the response
     *
     * @var string
     */
    public $message;

    /**
     * Holds any additional Data passed to response
     *
     * @var Collection
     */
    protected $with;

    /**
     * Create a  new instance of this class
     *
     * @param string $message
     * @param string $status
     */
    public function __construct(string $message, string $status = 'success')
    {
        $this->with = collect();
        $this->message = $message;
        $this->status = $status;
    }

    /**
     * Create a new instance of this class (Statically)
     *
     * @param string $message
     * @param string $status
     * @return InternalAppResponse
     */
    public static function send(string $message, string $status = 'success') : InternalAppResponse
    {
        return new static($message, $status);
    }

    /**
     * Pass additional Data to reponse
     *
     * @param array $data
     * @return InternalAppResponse
     */
    public function with(array $data) : InternalAppResponse
    {
        $this->with = collect($data);
        return $this;
    }

    /**
     * Determine if the response is a successful response
     *
     * @return boolean
     */
    public function success() : bool
    {
        return Str::lower($this->status) === 'success';
    }

    /**
     * Determine if the response is not a successful response
     *
     * @return boolean
     */
    public function error() : bool
    {
        return Str::lower($this->status) !== 'success';
    }

    /**
     * Check if response has additional data
     *
     * @return boolean
     */
    public function hasAdditionalData() : bool
    {
        return $this->with->isNotEmpty();
    }

    /**
     * Get response additional data record
     *
     * @return Collection
     */
    public function getAdditionalData() : Collection
    {
        return $this->with;
    }
}
