<?php

namespace App\Entities;

use App\Traits\WireMe;
use Livewire\Wireable;

class Stats implements Wireable
{
    use WireMe;

    public function __construct(
        public string $cpu = "",
        public string $memory = "",
    ) {
    }
}
