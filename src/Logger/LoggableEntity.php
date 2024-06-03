<?php

namespace App\Logger;

abstract class LoggableEntity
{

    public ?int $id = null;

    public function toLog()
    {
        return $this->__toString();
    }

}
