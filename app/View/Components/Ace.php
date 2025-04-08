<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Ace extends Component
{
    public function __construct(
        public string $id = '',
        public ?string $label = null,
        public string $language = 'javascript',
        public string $height = '200px',
        public string $model = '',
        public ?string $hint = ''
    ) {
        $this->id = $id ?: 'monaco-editor2-' . uniqid();
    }

    public function modelName(): ?string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
    }

    public function render(): View|Closure|string
    {
        return <<<'BLADE'
            <div>
                @if($label)
                    <div class="text-xs font-semibold mt-5 mb-3">{{ $label }}</div>
                @endif

                <div {{ $attributes->whereStartsWith('class')->class(["textarea w-full p-0", "textarea-error" => $errors->has($modelName())]) }} >
                    <div
                        wire:ignore
                        x-data="{
                            editor: null,
                            modelValue: @entangle($attributes->wire('model')),
                            init() {
                                ace.require('ace/ext/language_tools');
                                this.editor = ace.edit($refs.editor);
                                this.editor.setTheme('ace/theme/github_light_default')
                                this.editor.session.setMode('ace/mode/{{ $language }}');
                                this.editor.setShowPrintMargin(false);
                                this.editor.container.style.lineHeight = 2;
                                this.editor.renderer.setScrollMargin(10, 10);

                                this.editor.setOptions({
                                    enableBasicAutocompletion: true,
                                    enableLiveAutocompletion: true,
                                    enableSnippets: true,
                                });

                                this.editor.setValue(this.modelValue || '', -1);

                                this.editor.session.on('change', () => {
                                    this.modelValue = this.editor.getValue();
                                });

                                this.$watch('modelValue', value => {
                                    if (this.editor.getValue() !== value) {
                                        this.editor.setValue(value || '', -1);
                                    }
                                });
                            }
                        }"
                        x-init="init()"
                    >
                        <div x-ref="editor" id="{{ $id }}" style="width: 100%; height: {{ $height }}"></div>
                    </div>
                </div>

                @error($modelName())
                    <div class="text-error text-xs mt-3">{{ $message }}</div>
                @enderror

                @if($hint)
                    <div class="text-xs text-base-content/50 mt-2">{{ $hint }}</div>
                @endif
            </div>
        BLADE;
    }
}
