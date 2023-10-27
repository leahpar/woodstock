<?php

namespace App\Search;

use ReflectionProperty;

trait HydrateTrait
{
    public function hydrate(array $data): void
    {
        foreach ($data as $key => $value) {
            $method = 'set'.ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
            elseif (property_exists($this, $key) && $value !== "") {
                //$rp = new ReflectionProperty(self::class, $key);
                //switch ($rp->getType()->getName()) {
                //    case "bool":
                //        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                //        break;
                //}
                $this->{$key} = $value;
            }
        }
    }

}
