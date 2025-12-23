<x-layouts.app>
    <x-layouts.header :title="__('daily_travel.admin_review_title')" class="mb-4">
        <x-slot:actions>
            <a class="btn btn-secondary" href="{{ route('admin.daily-travels.index') }}">
                {{ __('daily_travel.back_to_list') }}
            </a>
        </x-slot:actions>
    </x-layouts.header>

    @if (session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid md:grid-cols-2 gap-4 mb-6">
        <div class="card bg-base-200">
            <div class="card-body space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="card-title m-0">{{ __('daily_travel.review_overview_title') }}</h3>
                    <span class="badge {{ $dailyTravel->isApproved() ? 'badge-success' : 'badge-warning' }}">
                        {{ __('daily_travel.status_' . $dailyTravel->approvalStatus()) }}
                    </span>
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <p class="text-xs uppercase text-base-content/60">{{ __('daily_travel.admin_user_label') }}</p>
                        <p class="font-semibold">{{ $dailyTravel->user?->name }}</p>
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
                        <p class="text-xs uppercase text-base-content/60">{{ __('daily_travel.preview_cost_per_km') }}</p>
                        <p class="font-semibold">€ {{ number_format((float) $structure?->cost_per_km, 4) }}</p>
                    </div>
                </div>
                @if ($dailyTravel->isApproved())
                    <div class="text-sm text-base-content/70">
                        {{ __('daily_travel.admin_review_approved_by', [
                            'user' => $dailyTravel->approver?->name ?? '-'],
                        ) }}
                        <span class="ml-1">{{ $dailyTravel->approved_at?->format('d/m/Y H:i') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="card bg-base-200">
            <div class="card-body space-y-3">
                <h3 class="card-title m-0">{{ __('daily_travel.review_route_title') }}</h3>
                <div class="space-y-2">
                    @forelse ($routeSteps as $step)
                        <div class="p-3 bg-base-100 border border-base-200 rounded-lg">
                            <p class="text-xs uppercase text-base-content/60">#{{ $step->step_number }}</p>
                            <p class="font-semibold">{{ $step->name }}</p>
                            <p class="text-sm text-base-content/70">{{ $step->address }} - {{ $step->city }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-base-content/70">{{ __('daily_travel.preview_steps_empty') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-300">
        <div class="card-body">
            <h3 class="card-title">{{ __('daily_travel.review_legs_title') }}</h3>

            @if ($errors->any())
                <div class="alert alert-error text-sm mb-4">
                    {{ __('daily_travel.review_validation_error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.daily-travels.review.update', $dailyTravel) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('daily_travel.review_from') }}</th>
                                <th>{{ __('daily_travel.review_to') }}</th>
                                <th>{{ __('daily_travel.review_distance_km') }}</th>
                                <th>{{ __('daily_travel.review_travel_minutes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($routeLegs as $index => $leg)
                                @php
                                    $toStep = $leg['to'];
                                    $defaultDistance = $leg['distance'] === null
                                        ? ''
                                        : number_format($leg['distance'], 2, '.', '');
                                    $distanceValue = old(
                                        'steps.' . $toStep->id . '.distance_km',
                                        $toStep->distance_km ?? $defaultDistance
                                    );
                                    $minutesValue = old(
                                        'steps.' . $toStep->id . '.travel_minutes',
                                        $toStep->travel_minutes
                                    );
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $leg['from']->city }} - {{ $leg['from']->address }}</td>
                                    <td>{{ $leg['to']->city }} - {{ $leg['to']->address }}</td>
                                    <td>
                                        <input type="number" step="0.01" min="0" class="input input-bordered w-full"
                                            name="steps[{{ $toStep->id }}][distance_km]" value="{{ $distanceValue }}" />
                                    </td>
                                    <td>
                                        <input type="number" step="1" min="0" class="input input-bordered w-full"
                                            name="steps[{{ $toStep->id }}][travel_minutes]" value="{{ $minutesValue }}" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-base-content/70">
                                        {{ __('daily_travel.review_legs_empty') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col sm:flex-row gap-2">
                    <button type="submit" class="btn btn-primary w-full sm:w-auto">
                        {{ __('daily_travel.review_save_approve') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
