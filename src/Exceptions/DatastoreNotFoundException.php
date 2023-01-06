<?php

namespace LaravelGeoserver\LaravelGeoserver\Exceptions;

use Exception;

class DatastoreNotFoundException extends Exception
{
    protected $message = 'Datastore not found!';
}
