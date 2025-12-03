<x-layouts.app>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-3xl font-semibold">{{ __('daily_travel.show_title') }}</h1>
        <div class="flex gap-2">
            <a class="btn btn-primary" href="{{ route('daily-travels.index') }}">{{ __('daily_travel.back_to_list') }}</a>
            <form method="POST" action="{{ route('daily-travels.destroy', $dailyTravel) }}"
                onsubmit="return confirm('{{ __('daily_travel.delete_confirm') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error" aria-label="{{ __('daily_travel.delete') }}">
                    <x-lucide-trash-2 class="w-4 h-4" />
                </button>
            </form>
        </div>
    </div>

    <hr>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="card bg-base-200">
            <div class="card-body space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="card-title m-0">{{ __('daily_travel.preview_title') }}</h3>
                    <span class="badge badge-outline">{{ __('daily_travel.preview_read_only') }}</span>
                </div>
                <hr>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <p class="text-xs uppercase text-base-content/60">{{ __('daily_travel.company_label') }}</p>
                        <p class="font-semibold">{{ $dailyTravel->company?->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-base-content/60">{{ __('daily_travel.travel_date') }}</p>
                        <p class="font-semibold">{{ $dailyTravel->travel_date?->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-base-content/60">{{ __('daily_travel.preview_vehicle') }}</p>
                        <p class="font-semibold">
                            {{ $structure?->vehicle ? $structure->vehicle->brand . ' ' . $structure->vehicle->model : __('daily_travel.preview_vehicle_none') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-base-content/60">{{ __('daily_travel.preview_cost_per_km') }}
                        </p>
                        <p class="font-semibold">€ {{ number_format((float) $structure?->cost_per_km, 4) }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-base-content/60">
                            {{ __('daily_travel.preview_economic_value') }}</p>
                        <p class="font-semibold">€ {{ number_format((float) $structure?->economic_value, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-base-200">
            <div class="card-body">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-semibold">{{ __('daily_travel.preview_steps_title') }}</h4>
                    <span class="badge badge-outline">{{ __('daily_travel.preview_read_only') }}</span>
                </div>
                <hr>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th class="w-12">#</th>
                                <th>{{ __('daily_travel.steps_address') }}</th>
                                <th>{{ __('daily_travel.steps_city') }}</th>
                                <th>{{ __('daily_travel.steps_province') }}</th>
                                <th>{{ __('daily_travel.steps_zip') }}</th>
                                <th>{{ __('daily_travel.steps_economic_value') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($steps as $step)
                                <tr>
                                    <td class="w-12">{{ $step->step_number }}</td>
                                    <td>{{ $step->address }}</td>
                                    <td>{{ $step->city }}</td>
                                    <td>{{ $step->province }}</td>
                                    <td>{{ $step->zip_code }}</td>
                                    <td>€ {{ number_format((float) $step->economic_value, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-sm text-base-content/70">
                                        {{ __('daily_travel.preview_steps_empty') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4 mt-4">
        <div class="card bg-base-200">
            <div class="card-body space-y-3">
                <div class="flex items-center gap-2">
                    <h4 class="font-semibold">{{ __('daily_travel.distance_summary_title') }}</h4>
                </div>
                <hr>
                <div class="space-y-2">
                    @forelse ($distancesBetweenSteps as $distance)
                        <div class="p-3 rounded-lg bg-base-100 border border-base-200">
                            <div class="text-xs uppercase text-gray-500 mb-1">
                                {{ __('daily_travel.distance_summary_path') }}
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="badge badge-outline">
                                    {{ $distance['from']->city }} - {{ $distance['from']->address }}
                                </div>
                                <x-lucide-arrow-right class="w-4 h-4 text-gray-500" />
                                <div class="badge badge-outline">
                                    {{ $distance['to']->city }} - {{ $distance['to']->address }}
                                </div>
                            </div>
                            <div class="mt-2 text-sm">
                                <span class="font-medium">{{ __('daily_travel.distance_summary_distance') }}:</span>
                                {{ number_format($distance['distance'], 2) }} km
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-base-content/70">{{ __('daily_travel.distance_summary_empty') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card bg-base-200">
            <div class="card-body space-y-4">
                <h4 class="font-semibold mb-2">{{ __('daily_travel.map_title') }}</h4>
                <hr>
                <div id="daily-travel-map" class="h-80 w-full rounded-lg bg-base-300"
                    data-steps='@json($mapSteps)' data-api-key="{{ $googleMapsApiKey }}">
                    <p class="p-4 text-sm text-gray-500">{{ __('daily_travel.map_placeholder') }}</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/daily-travel-structure.js')
    @endpush
</x-layouts.app>
