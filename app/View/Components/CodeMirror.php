<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CodeMirror extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $mode = 'yaml',
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    /**
     * Get the view / contents that represent the component.
     */

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div
                    x-data="
                            {
                                value: @entangle($attributes->wire('model')),
                                editor: null,
                                init() {
                                    this.editor = CodeMirror(this.$refs.codemirror{{ $uuid }}, {
                                                       lineNumbers: true,
                                                        value: this.value,
                                                        mode: '{{ $mode }}',
                                                        viewportMargin: Infinity,
                                                 });

                                    this.editor.on('change', (editor) => this.value = editor.doc.getValue());
                                },
                                destroy() {
                                    this.editor.getWrapperElement().remove();
                                }
                            }"
                    wire:ignore
                >
                    <div x-ref="codemirror{{ $uuid }}" {{ $attributes->class(["textarea textarea-primary py-4"]) }}></div>
                </div>
        HTML;
    }
}
