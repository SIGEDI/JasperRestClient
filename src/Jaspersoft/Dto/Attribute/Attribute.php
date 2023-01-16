<?php

namespace Jaspersoft\Dto\Attribute;

/**
 * Represents a user attribute.
 */
class Attribute
{
    public $name;
    public $value;

    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }
}
