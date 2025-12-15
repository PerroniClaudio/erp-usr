<x-layouts.app>
    <x-layouts.header :title="__('business_trips.admin_index_title')" id="page-header" />


    <div class="card bg-base-300 mb-6">
        <div class="card-body">
            <div class="card-title">{{ __('business_trips.admin_filters_title') }}</div>
            <form method="GET" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('business_trips.admin_user_label') }}</legend>
                    <select name="user_id" class="select select-bordered w-full">
                        <option value="">{{ __('business_trips.admin_user_placeholder') }}</option>
                        @foreach ($users as $userOption)
                            <option value="{{ $userOption->id }}" @selected($selectedUser && $selectedUser->id === $userOption->id)>
                                {{ $userOption->name }}
                            </option>
                        @endforeach
                    </select>
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('business_trips.admin_year_label') }}</legend>
                    <select name="year" class="select select-bordered w-full">
                        @foreach (range(\Carbon\Carbon::now()->year - 5, \Carbon\Carbon::now()->year + 5) as $yearOption)
                            <option value="{{ $yearOption }}" @selected($selectedYear === $yearOption)>
                                {{ $yearOption }}</option>
                        @endforeach
                    </select>
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('business_trips.admin_month_label') }}</legend>
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
                    <button type="submit" class="btn btn-primary w-full sm:w-auto">
                        {{ __('business_trips.admin_submit') }}
                    </button>
                    @if ($selectedUser)
                        <a class="btn btn-secondary w-full sm:w-auto"
                            href="{{ route('admin.business-trips.export', [
                                'user_id' => $selectedUser->id,
                                'year' => $selectedYear,
                                'month' => $selectedMonth,
                            ]) }}">
                            <x-lucide-file-text class="w-4 h-4" />
                            <span class="ml-2">{{ __('business_trips.export_nota_spese') }}</span>
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
                        {{ __('business_trips.admin_results_title', [
                            'user' => $selectedUser->name,
                            'period' => ucfirst(
                                \Carbon\Carbon::createFromDate($selectedYear, (int) $selectedMonth, 1)->locale('it')->translatedFormat('F Y'),
                            ),
                        ]) }}
                    </h2>
                </div>

                @if ($businessTrips->isEmpty())
                    <div class="text-base-content/70">
                        {{ __('business_trips.admin_empty') }}
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('business_trips.code') }}</th>
                                    <th>{{ __('business_trips.date_from') }}</th>
                                    <th>{{ __('business_trips.date_to') }}</th>
                                    <th>{{ __('business_trips.status') }}</th>
                                    <th>{{ __('business_trips.admin_transfers') }}</th>
                                    <th>{{ __('business_trips.admin_expenses_total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($businessTrips as $trip)
                                    <tr>
                                        <td>{{ $trip->code }}</td>
                                        <td>{{ \Carbon\Carbon::parse($trip->date_from)->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($trip->date_to)->format('d/m/Y') }}</td>
                                        <td>{{ $trip->getStatus() }}</td>
                                        <td>{{ $trip->transfers->count() }}</td>
                                        <td>€ {{ number_format($trip->expenses->sum('amount'), 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            @if ($totals)
                                <tfoot>
                                    <tr class="font-semibold">
                                        <td>{{ __('business_trips.admin_totals') }}</td>
                                        <td colspan="2">{{ $totals['trips'] }}
                                            {{ \Illuminate\Support\Str::lower(__('business_trips.business_trips')) }}
                                        </td>
                                        <td>{{ $totals['transfers'] }} {{ __('business_trips.admin_transfers') }}</td>
                                        <td colspan="2">€ {{ number_format($totals['expenses'], 2, ',', '.') }}</td>
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
