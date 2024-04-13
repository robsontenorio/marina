<?php

use Carbon\Carbon;
use Livewire\Volt\Component;

new class extends Component {
    public mixed $task;
}; ?>

<div>
    <div>
        <div @class(["flex justify-between items-center gap-3"])>
            <div>
                <x-icon name="o-cube" />
            </div>
            <div class="flex-1 flex gap-3 items-center">
                <span class="font-bold">{{ $task['name'] }}</span>
                
                <x-badge class="badge-sm mr-2 {{ $task['color'] }}" :value="$task['state']" />

                <span data-tip="This task is updating" @class(["hidden", "tooltip !inline-block" => $task['is_updating']])>
                    <x-loading class="loading-ring -mb-2" />
                </span>

                <span class="text-xs text-gray-500 tooltip" data-tip="{{ Carbon::parse($task['created_at'])->format('Y-m-d H:i:s') }}">{{ $task['created_at'] }}</span>

                <div @class(["hidden", "text-xs text-error !block" => $task['error_message'] ?? ''])>
                    {{ $task['error_message'] ?? '' }}
                </div>
            </div>

            {{--  Removing warning --}}
            <div @class(["hidden", "!inline-flex" => $task['will_remove']])>
                <x-badge value="removing" class="badge-sm bg-error/40" />
            </div>

            <div @class(["hidden", "!inline-flex" => $task['is_running']])>
                <span class="tooltip" data-tip="cpu / mem">
                    <x-icon name="o-cpu-chip" label="{{ $task['stats']['cpu'] ?? '-' }} / {{ $task['stats']['mem'] ?? '-' }}" class="text-xs" />
                </span>
            </div>
        </div>
    </div>
</div>
