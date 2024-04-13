<?php

namespace App\Traits;

trait WireMe
{
    public static function fromLivewire($value)
    {
        return new self(...$value);
    }

    public function toLivewire()
    {
        return (array) $this;
    }
}
