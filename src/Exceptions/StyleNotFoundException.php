<?php

namespace LaravelGeoserver\LaravelGeoserver\Exceptions;

use Exception;

class StyleNotFoundException extends Exception
{
    protected $message = 'Style not found!';
}
