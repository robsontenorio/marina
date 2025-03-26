<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CodeMirror extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $mode = 'yaml',
        public ?string $hint = ''
    ) {
        $this->uuid = "mary" . md5(serialize($this));
    }

    public function modelName(): ?string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
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
                                    this.editor = new CodeMirror(this.$refs.codemirror{{ $uuid }}, {
                                                   lineNumbers: true,
                                                   value: this.value || '\n\n\n',
                                                   mode: '{{ $mode }}',
                                                   viewportMargin: Infinity,
                                             });

                                    this.editor.on('change', () => this.$nextTick(() => this.value = this.editor.doc.getValue()));
                                },
                                destroy() {
                                    this.editor.getWrapperElement().remove();
                                }
                            }"
                >
                    @if($label)
                        <div class="text-xs font-semibold mt-5 mb-3">{{ $label }}</div>
                    @endif

                    <div {{ $attributes->class(["textarea w-full", "textarea-error" => $errors->has($modelName())]) }} >
                        <div x-ref="codemirror{{ $uuid }}" wire:ignore ></div>
                    </div>

                    @error($modelName())
                        <div class="text-error text-xs mt-3">{{ $message }}</div>
                    @enderror

                    @if($hint)
                        <div class="text-xs text-base-content/50 mt-2">{{ $hint }}</div>
                    @endif
                </div>
        HTML;
    }
}
