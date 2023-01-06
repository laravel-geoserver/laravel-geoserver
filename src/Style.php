<?php

namespace LaravelGeoserver\LaravelGeoserver;

class Style
{
    private $isSaved = false;
    private $name = '';
    private $oldName = '';
    private $styleContent = '';
    private $workspace;
    private $forbiddenSet = ['forbiddenSet', 'oldName'];

    public function __construct(string $name, $workspaceName = null, $isSaved = false)
    {
        $this->name = $name;
        $this->oldName = $name;
        if ($workspaceName) {
            $this->workspace = GeoserverClient::workspace($workspaceName);
        }
        $this->isSaved = $isSaved;
    }

    public static function create(...$params)
    {
        return new static(...$params);
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value)
    {
        if ($property == 'styleContent') {
            $this->isSaved = false;
        }
        if (property_exists($this, $property) && ! in_array($property, $this->forbiddenSet)) {
            $property === 'name' && $this->oldName = $this->name;
            $this->isSaved = ! ($this->$property !== $value);
            $this->$property = $value;
        }

        return $this;
    }

    public function save()
    {
        return GeoserverClient::saveStyle($this);
    }

    public function delete()
    {
        if ($this->workspace) {
            $this->isSaved = ! GeoserverClient::deleteStyle($this->name, $this->workspace->name);
        } else {
            $this->isSaved = ! GeoserverClient::deleteStyle($this->name);
        }

        $this->name = $this->oldName;

        return ! $this->isSaved;
    }
}
