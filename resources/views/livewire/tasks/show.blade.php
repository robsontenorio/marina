<?php

use Carbon\Carbon;
use Livewire\Volt\Component;

new class extends Component {
    public bool $current = false;

    public mixed $service;

    public mixed $task;
}; ?>

<div>
    <div>
        <div @class(["flex justify-between items-center gap-3"])>
            <div>
                <x-icon name="{{ $current ? 'o-cube' : 'o-cube-transparent' }}" />
            </div>
            <div @class(["flex-1 flex gap-3 items-center", "text-xs italic" => !$current])>
                <span class="font-bold">{{ $service['Spec']['Name'] }}.{{ $task['Slot'] }}</span>

                {{-- Current State --}}
                <x-badge class="badge-sm mr-2 {{ $task['color'] }}" :value="$task['Status']['State']" />
                <span data-tip="This task is updating" @class(["tooltip", "hidden" => ($task['DesiredState'] == $task['Status']['State']) || (($task['Status']['Err'] ??  '') != '')])>
                    <x-loading class="loading-ring loading-xs" />
                </span>

                <span class="text-xs text-gray-500 tooltip" data-tip="{{ Carbon::parse($task['CreatedAt'])->format('Y-m-d H:i:s') }}">{{ $task['diff_timestamp'] }}</span>

                <div @class(["hidden", "text-xs text-error !block" => $task['Status']['Err'] ?? ''])>
                    {{ $task['Status']['Err'] ?? '' }}
                </div>
                {{--                <div class="text-xs">--}}
                {{--                    Container: <span>{{ $task['container_id'] }}</span>--}}
                {{--                </div>--}}
            </div>

            {{--  Removing warning --}}
            <div @class(["hidden", "!inline-flex" => $task['DesiredState'] == 'remove'])>
                <x-badge value="removing" class="badge-sm bg-error/40" />
            </div>

            <div @class(["hidden", "!inline-flex" => $task['Status']['State'] == 'running'])>
                <span class="tooltip" data-tip="cpu / mem">
                    <x-icon name="o-cpu-chip" label="{{ $task['stats']['cpu'] ?? '-' }} / {{ $task['stats']['mem'] ?? '-' }}" class="text-xs" />
                </span>
            </div>
        </div>
        {{--        <x-collapse>--}}
        {{--            <x-slot:heading>--}}
        {{--                <div class="font-bold">SERVICE</div>--}}
        {{--            </x-slot:heading>--}}
        {{--            <x-slot:content>--}}
        {{--                <pre>{{ json_encode($service, JSON_PRETTY_PRINT) }}</pre>--}}
        {{--            </x-slot:content>--}}
        {{--        </x-collapse>--}}
        {{--        <x-collapse>--}}
        {{--            <x-slot:heading>--}}
        {{--                <div class="font-bold">Inspect</div>--}}
        {{--            </x-slot:heading>--}}
        {{--            <x-slot:content>--}}
        {{--                <pre>{{ json_encode($task['inspect'], JSON_PRETTY_PRINT) }}</pre>--}}
        {{--            </x-slot:content>--}}
        {{--        </x-collapse>--}}
        {{--        <x-collapse>--}}
        {{--            <x-slot:heading>--}}
        {{--                <div class="font-bold">Logs ({{ $task['ID'] }})</div>--}}
        {{--            </x-slot:heading>--}}
        {{--            <x-slot:content>--}}
        {{--                <div class="overflow-y-auto h-[96]">--}}
        {{--                                                    <pre style="background-color: black; overflow: auto; padding: 10px 15px; font-family: monospace;">--}}
        {{--                                                        {!!  $task['logs'] ?? '-' !!}--}}
        {{--                                                    </pre>--}}
        {{--                </div>--}}
        {{--            </x-slot:content>--}}
        {{--        </x-collapse>--}}
    </div>
</div>
