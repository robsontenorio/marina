<?php

use Livewire\Volt\Component;

new class extends Component {
    public mixed $service;

    public mixed $task;
}; ?>

<div>
    <div>
        <div @class(['text-xs', 'bg-red-50 animate-pulse' => $task['DesiredState'] == 'remove'])>
            <span class="font-bold">{{ $service['Spec']['Name'] }}.{{ $task['Slot'] }}</span>

            {{-- Current State --}}
            <span class="badge {{ $task['color'] }} mr-2">{{ $task['Status']['State'] }}</span>

            {{-- Desired State --}}
            <span @class(['text-sm hidden animate-pulse', '!inline-block' => $task['DesiredState'] != $task['Status']['State']])>
                <x-icon name="o-arrow-right" />
                <span class="badge">{{ $task['DesiredState'] }}</span>
            </span>

            <span @class(["hidden" => ($task['DesiredState'] == $task['Status']['State']) || (($task['Status']['Err'] ??  '') != '')])>
                <x-loading />
            </span>
            <span>{{ $task['diff_timestamp'] }}</span>

            <div> Error: {{ $task['Status']['Err'] ?? '-' }} </div>
        </div>
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
        {{--                                            <pre style="background-color: black; overflow: auto; padding: 10px 15px; font-family: monospace;">--}}
        {{--                                                {!!  $task['logs'] ?? '-' !!}--}}
        {{--                                            </pre>--}}
        {{--                </div>--}}
        {{--            </x-slot:content>--}}
        {{--        </x-collapse>--}}
    </div>
</div>
