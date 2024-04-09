<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AppBrand extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <a href="/" wire:navigate>
                    <!-- Hidden when collapsed -->
                    <div {{ $attributes->class(["hidden-when-collapsed"]) }}>
                        <div class="flex gap-2">
                            <img src="/images/waves.png" width="30" class="mt-1" />
                            <span class="font-bold text-3xl mr-3 bg-gradient-to-r from-blue-600 to-blue-400 bg-clip-text text-transparent ">
                                marina
                            </span>
                        </div>
                    </div>

                    <!-- Display when collapsed -->
                    <div class="display-when-collapsed hidden mx-5 mt-4 lg:mb-6 h-[28px]">
                        <img src="/images/waves.png" width="30" class="h-8" />
                    </div>
                </a>
            HTML;
    }
}
