@props([
    'businessTrip' => null,
])

<div class="card bg-base-300 max-w-full">
    <div class="card-body">
        <div class="flex items-center justify-between">
            <h2 class="text-xl">
                {{ __('business_trips.business_trip_expenses') }}
            </h2>

            <a href="{{ route('business-trips.expenses.create', [
                'businessTrip' => $businessTrip->id,
            ]) }}"
                class="btn btn-primary">
                <x-lucide-plus class="w-4 h-4" />
            </a>
        </div>

        <hr>

        <div class="max-w-full lg:max-w-full overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>{{ __('business_trips.id') }}</th>
                        <th class="hidden lg:table-cell">{{ __('business_trips.company') }}</th>
                        <th>{{ __('business_trips.date') }}</th>
                        <th class="hidden lg:table-cell">{{ __('business_trips.address') }}</th>
                        <th class="hidden lg:table-cell">{{ __('business_trips.payment_type') }}</th>
                        <th class="hidden lg:table-cell">{{ __('business_trips.expense_type') }}</th>
                        <th>{{ __('business_trips.amount') }}</th>
                        <th>{{ __('business_trips.actions') }}</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>

    </div>
</div>
