<?php

namespace Jaspersoft\Dto\Resource;

/**
 * Class Resource.
 */
class Resource
{
    public $uri;
    public $label;
    public $description;
    public $permissionMask;
    public $creationDate;
    public $updateDate;
    public $version;

    public static function createFromJSON($json_data, $type = null)
    {
        $result = (empty($type)) ? new self() : new $type();
        foreach ($json_data as $k => $v) {
            $result->$k = $v;
        }

        return $result;
    }

    public function toJSON()
    {
        return json_encode($this->jsonSerialize());
    }

    public function jsonSerialize(): array
    {
        $result = [];
        foreach (get_object_vars($this) as $k => $v) {
            if (isset($v)) {
                $result[$k] = $v;
            }
        }

        return $result;
    }

    public function name(): string
    {
        $type = explode('\\', get_class($this));

        return lcfirst(end($type));
    }

    public static function className(): string
    {
        $type = explode('\\', get_called_class());

        return lcfirst(end($type));
    }

    public function contentType(): string
    {
        return 'application/repository.'.$this->name().'+json';
    }
}
