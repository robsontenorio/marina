<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>
    <div id="container" class="h-96 textarea textarea-primary -pb-20">
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.47.0/min/vs/loader.min.js"></script>
    <script>
        require.config({paths: {vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.47.0/min/vs'}});
        require(['vs/editor/editor.main'], function () {
            var editor = monaco.editor.create(document.getElementById('container'), {
                value: ['function x() {', '\tconsole.log("Hello world!");', '}'].join('\n'),
                language: 'javascript',
                minimap: {enabled: false},
            });
        });
    </script>

</div>
