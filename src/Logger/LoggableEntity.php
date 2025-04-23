<?php

namespace App\Logger;

abstract class LoggableEntity
{

    public ?int $id = null;

    abstract public function __toString(): string;

    public function toLog()
    {
        return $this->__toString();
    }

}
