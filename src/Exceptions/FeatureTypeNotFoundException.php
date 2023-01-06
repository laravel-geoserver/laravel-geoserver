<?php

namespace LaravelGeoserver\LaravelGeoserver\Exceptions;

use Exception;

class FeatureTypeNotFoundException extends Exception
{
    protected $message = 'Feature type not found!';
}
