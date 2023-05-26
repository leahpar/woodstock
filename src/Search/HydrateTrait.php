<?php

namespace App\Search;

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
                $this->{$key} = $value;
            }
        }
    }

}
