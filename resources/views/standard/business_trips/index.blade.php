<x-layouts.app>

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('business_trips.business_trips') }}</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('business-trips.create') }}" class="btn btn-primary">
                {{ __('business_trips.business_trip_create') }}
            </a>
            <button class="btn btn-primary" onclick="export_nota_spese.showModal()">
                <x-lucide-file-text class="w-4 h-4" />
                {{ __('business_trips.export_nota_spese') }}
            </button>
        </div>
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
                            <td class="hidden lg:table-cell">{{ $businessTrip->getStatus() }}</td>

                            <td>
                                <a href="{{ route('business-trips.edit', $businessTrip) }}">
                                    <div class="btn btn-primary">
                                        <x-lucide-pencil class="w-4 h-4" />
                                    </div>
                                </a>
                                @if ($businessTrip->expenses->isEmpty() && $businessTrip->transfers->isEmpty())
                                    <form action="{{ route('business-trips.destroy', $businessTrip) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-warning"
                                            onclick="return confirm('{{ __('business_trips.confirm_delete') }}')">
                                            <x-lucide-trash-2 class="w-4 h-4" />
                                        </button>
                                    </form>
                                @endif
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
    </div>

    <dialog id="export_nota_spese" class="modal">
        <div class="modal-box">
            <div class="flex flex-row-reverse items-end">
                <form method="dialog">
                    <!-- if there is a button in form, it will close the modal -->
                    <button class="btn btn-ghost">
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </form>
            </div>
            <h1 class="text-3xl mb-4">{{ __('business_trips.export_nota_spese') }}</h1>
            <hr>
            <form action="{{ route('business-trips.pdf-batch') }}" method="GET">
                <fieldset class="fieldset mb-4">
                    <legend class="fieldset-legend">{{ __('personnel.users_cedolino_year') }}</legend>
                    <select id="year" name="year" class="select select-bordered">
                        @foreach (range(\Carbon\Carbon::now()->year - 5, \Carbon\Carbon::now()->year + 5) as $year)
                            <option value="{{ $year }}" @if ($year == \Carbon\Carbon::now()->year) selected @endif>
                                {{ $year }}</option>
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.users_cedolino_month') }}</legend>
                    <select id="month" name="month" class="select select-bordered">
                        @foreach (range(1, 12) as $month)
                            <option value="{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}"
                                @if ($month == \Carbon\Carbon::now()->subMonth()->month) selected @endif>
                                {{ ucfirst(\Carbon\Carbon::create()->month($month)->locale('it')->translatedFormat('F')) }}
                            </option>
                        @endforeach
                    </select>
                </fieldset>


                <button type="submit" class="btn btn-primary">
                    {{ __('business_trips.export_nota_spese') }}
                </button>

            </form>
        </div>
    </dialog>

</x-layouts.app>
