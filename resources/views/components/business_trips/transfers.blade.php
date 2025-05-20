@props([
    'businessTrip' => null,
    'transfers' => [],
])

<div class="card bg-base-300 max-w-full">
    <div class="card-body">
        <div class="flex items-center justify-between">
            <h2 class="text-xl">
                {{ __('business_trips.business_trip_transfers') }}
            </h2>

            <a href="{{ route('business-trips.transfers.create', [
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
                        <th>{{ __('business_trips.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @unless ($transfers->count())
                        <tr>
                            <td colspan="8" class="text-center">
                                {{ __('business_trips.no_transfers') }}
                            </td>
                        </tr>
                    @endunless
                    @foreach ($transfers as $transfer)
                        <tr>
                            <td>{{ $transfer->id }}</td>
                            <td class="hidden lg:table-cell">
                                {{ $transfer->company ? $transfer->company->name : '-' }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($transfer->date)->format('d/m/Y') }}</td>
                            <td class="hidden lg:table-cell">{{ $transfer->address }}</td>

                            <td>
                                <a href="{{ route('business-trips.transfers.edit', [
                                    'businessTrip' => $businessTrip->id,
                                    'transfer' => $transfer->id,
                                ]) }}"
                                    class="btn btn-sm btn-primary">
                                    <x-lucide-pencil class="w-4 h-4" />
                                </a>

                                <form
                                    action="{{ route('business-trips.transfers.destroy', [
                                        'businessTrip' => $businessTrip->id,
                                        'transfer' => $transfer->id,
                                    ]) }}"
                                    method="POST" class="inline-block"
                                    onsubmit="return confirm('{{ __('business_trips.confirm_delete') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-warning">
                                        <x-lucide-trash-2 class="w-4 h-4" />
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>
