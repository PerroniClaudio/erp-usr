<x-layouts.app>
    <x-layouts.header :title="__('daily_travel.admin_index_title')">
        <x-slot:actions>
            <a class="btn btn-primary"
                href="{{ route('admin.daily-travels.create', $selectedUser ? ['user_id' => $selectedUser->id] : []) }}">
                {{ __('daily_travel.admin_new_travel') }}
            </a>
        </x-slot:actions>
    </x-layouts.header>
    <div class="card bg-base-300 mb-6">
        <div class="card-body">
            <div class="card-title">{{ __('daily_travel.admin_filters_title') }}</div>
            <form method="GET" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('daily_travel.admin_user_label') }}</legend>
                    <select name="user_id" class="select select-bordered w-full">
                        <option value="">{{ __('daily_travel.admin_user_placeholder') }}</option>
                        @foreach ($users as $userOption)
                            <option value="{{ $userOption->id }}"
                                @selected($selectedUser && $selectedUser->id === $userOption->id)>
                                {{ $userOption->name }}
                            </option>
                        @endforeach
                    </select>
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('daily_travel.admin_year_label') }}</legend>
                    <select name="year" class="select select-bordered w-full">
                        @foreach (range(\Carbon\Carbon::now()->year - 5, \Carbon\Carbon::now()->year + 5) as $yearOption)
                            <option value="{{ $yearOption }}" @selected($selectedYear === $yearOption)>
                                {{ $yearOption }}</option>
                        @endforeach
                    </select>
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('daily_travel.admin_month_label') }}</legend>
                    <select name="month" class="select select-bordered w-full">
                        @foreach (range(1, 12) as $monthOption)
                            @php
                                $monthValue = str_pad($monthOption, 2, '0', STR_PAD_LEFT);
                            @endphp
                            <option value="{{ $monthValue }}" @selected($selectedMonth === $monthValue)>
                                {{ ucfirst(\Carbon\Carbon::create()->month($monthOption)->locale('it')->translatedFormat('F')) }}
                            </option>
                        @endforeach
                    </select>
                </fieldset>

                <div class="flex gap-2 flex-col sm:flex-row col-span-full">
                    <button type="submit" class="btn btn-primary w-full lg:w-auto">
                        {{ __('daily_travel.admin_submit') }}
                    </button>
                    @if ($selectedUser)
                        <a class="btn btn-secondary w-full lg:w-auto"
                            href="{{ route('admin.daily-travels.export', [
                                'user_id' => $selectedUser->id,
                                'year' => $selectedYear,
                                'month' => $selectedMonth,
                            ]) }}">
                            <x-lucide-file-text class="w-4 h-4" />
                            <span class="ml-2">{{ __('daily_travel.export_nota_spese') }}</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if ($selectedUser)
        <div class="card bg-base-300">
            <div class="card-body">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="text-2xl">
                        {{ __('daily_travel.admin_results_title', [
                            'user' => $selectedUser->name,
                            'period' => ucfirst(\Carbon\Carbon::createFromDate($selectedYear, (int) $selectedMonth, 1)->locale('it')->translatedFormat('F Y')),
                        ]) }}
                    </h2>
                </div>

                @if ($travelsData->isEmpty())
                    <div class="empty-state">
                        {{ __('daily_travel.admin_empty') }}
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('daily_travel.travel_date') }}</th>
                                    <th>{{ __('daily_travel.start_location_label') }}</th>
                                    <th>{{ __('daily_travel.status_label') }}</th>
                                    <th>{{ __('daily_travel.total_distance') }}</th>
                                    <th>{{ __('daily_travel.distance_cost') }}</th>
                                    <th>{{ __('daily_travel.time_total_hours') }}</th>
                                    <th>{{ __('daily_travel.indemnity') }}</th>
                                    <th>{{ __('daily_travel.economic_value') }}</th>
                                    <th>{{ __('daily_travel.total_label') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($travelsData as $data)
                                    <tr>
                                        <td>{{ $data['travel']->travel_date?->format('d/m/Y') }}</td>
                                        <td>{{ __('daily_travel.start_location_' . ($data['start_location'] ?? \App\Models\DailyTravelStructure::START_LOCATION_OFFICE)) }}</td>
                                        <td>
                                            <span class="badge {{ $data['travel']->isApproved() ? 'badge-success' : 'badge-warning' }}">
                                                {{ __('daily_travel.status_' . $data['travel']->approvalStatus()) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($data['distance'], 2, ',', '.') }}</td>
                                        <td>€ {{ number_format($data['distance_cost'], 2, ',', '.') }}</td>
                                        <td>{{ number_format($data['travel_hours'], 2, ',', '.') }}</td>
                                        <td>€ {{ number_format($data['indemnity'], 2, ',', '.') }}</td>
                                        <td>€ {{ number_format($data['economic_value'], 2, ',', '.') }}</td>
                                        <td>€ {{ number_format($data['total'], 2, ',', '.') }}</td>
                                        <td class="text-right">
                                            <a class="btn btn-sm btn-primary"
                                                href="{{ route('admin.daily-travels.review', $data['travel']) }}">
                                                {{ __('daily_travel.admin_review_action') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            @if ($totals)
                                <tfoot>
                                    <tr class="font-semibold">
                                        <td colspan="4">{{ __('daily_travel.admin_totals') }}</td>
                                        <td>{{ number_format($totals['distance'], 2, ',', '.') }}</td>
                                        <td>€ {{ number_format($totals['distance_cost'], 2, ',', '.') }}</td>
                                        <td>{{ number_format($totals['travel_hours'], 2, ',', '.') }}</td>
                                        <td>€ {{ number_format($totals['indemnity'], 2, ',', '.') }}</td>
                                        <td>€ {{ number_format($totals['economic_value'], 2, ',', '.') }}</td>
                                        <td>€ {{ number_format($totals['grand_total'], 2, ',', '.') }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
</x-layouts.app>
