<?php

namespace LaravelGeoserver\LaravelGeoserver\Exceptions;

use Exception;

class TableNotFoundException extends Exception
{
    protected $message = 'Table not found!';
}
