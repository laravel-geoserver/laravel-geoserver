<?php

namespace LaravelGeoserver\LaravelGeoserver\Exceptions;

use Exception;

class NoContentInStyleException extends Exception
{
    protected $message = 'No sld content in style!';
}
