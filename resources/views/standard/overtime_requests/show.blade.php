<x-layouts.app>
    <x-layouts.header
        :title="__('overtime_requests.type') . ': ' . ($overtimeRequest->overtimeType->name ?? '') . ' (' . ($overtimeRequest->overtimeType->acronym ?? '') . ')'" />
    <div class="card bg-base-300 w-full md:w-1/2 xl:w-1/3">
        <div class="card-body flex flex-col gap-2 ">
            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('overtime_requests.date') }}</legend>
                <div class="p-2 bg-base-200 rounded">{{ $overtimeRequest->date }}</div>
            </fieldset>
            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('overtime_requests.time_in') }}</legend>
                <div class="p-2 bg-base-200 rounded">{{ $overtimeRequest->time_in }}</div>
            </fieldset>
            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('overtime_requests.time_out') }}</legend>
                <div class="p-2 bg-base-200 rounded">{{ $overtimeRequest->time_out }}</div>
            </fieldset>
            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('overtime_requests.hours') }}</legend>
                <div class="p-2 bg-base-200 rounded">{{ $overtimeRequest->hours }}</div>
            </fieldset>
            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('overtime_requests.company') }}</legend>
                <div class="p-2 bg-base-200 rounded">{{ $overtimeRequest->company->name ?? '' }}</div>
            </fieldset>
            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('overtime_requests.status') }}</legend>
                <div class="p-2 bg-base-200 rounded">
                    @if ($overtimeRequest->status == 0)
                        {{ __('overtime_requests.created') }}
                    @elseif($overtimeRequest->status == 1)
                        {{ __('overtime_requests.pending') }}
                    @elseif($overtimeRequest->status == 2)
                        {{ __('overtime_requests.approved') }}
                    @elseif($overtimeRequest->status == 3)
                        {{ __('overtime_requests.denied') }}
                    @endif
                </div>
            </fieldset>
            <a href="{{ route('overtime-requests.index') }}" class="btn btn-secondary mt-2">Torna all'elenco</a>
        </div>
    </div>
</x-layouts.app>
