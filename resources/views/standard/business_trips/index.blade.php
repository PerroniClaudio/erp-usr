<x-layouts.app>

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('business_trips.business_trips') }}</h1>
        <a href="{{ route('business-trips.create') }}" class="btn btn-primary">
            {{ __('business_trips.business_trip_create') }}
        </a>
    </div>

    <hr>

    <div>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('business_trips.id') }}</th>
                        <th>{{ __('business_trips.code') }}</th>
                        <th class="hidden lg:table-cell">{{ __('business_trips.date_from') }}</th>
                        <th class="hidden lg:table-cell">{{ __('business_trips.date_to') }}</th>
                        <th class="hidden lg:table-cell">{{ __('business_trips.status') }}</th>
                        <th>{{ __('business_trips.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($businessTrips as $businessTrip)
                        <tr>
                            <td>{{ $businessTrip->id }}</td>
                            <td>{{ $businessTrip->code }}</td>
                            <td class="hidden lg:table-cell">
                                {{ \Carbon\Carbon::parse($businessTrip->date_from)->format('d/m/Y') }}
                            </td>
                            <td class="hidden lg:table-cell">
                                {{ \Carbon\Carbon::parse($businessTrip->date_to)->format('d/m/Y') }}
                            </td>
                            <td class="hidden lg:table-cell">{{ $businessTrip->status }}</td>

                            <td>
                                <a href="{{ route('business-trips.edit', $businessTrip) }}">
                                    <div class="btn btn-primary">
                                        <x-lucide-pencil class="w-4 h-4" />
                                    </div>
                                </a>

                                <form action="{{ route('business-trips.destroy', $businessTrip) }}" method="POST"
                                    class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-warning"
                                        onclick="return confirm('{{ __('business_trips.confirm_delete') }}')">
                                        <x-lucide-trash-2 class="w-4 h-4" />
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class=" text-center">
                                {{ __('business_trips.no_records') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $businessTrips->links() }}
        </div>


        @push('scripts')
            @vite('resources/js/businessTrips.js')
        @endpush

</x-layouts.app>
