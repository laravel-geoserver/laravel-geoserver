<?php

namespace LaravelGeoserver\LaravelGeoserver\Exceptions;

use Exception;

class ErrorCreatingStyleException extends Exception
{
    protected $message = 'An error occured while creating the style!';

    public function __construct($message = null)
    {
        if ($message) {
            $this->message = $message;
        }
    }
}
