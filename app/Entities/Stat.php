<?php

namespace App\Entities;

class Stat
{
    public function __construct(
        public string $cpu = "",
        public string $memory = "",
    ) {
    }
}
