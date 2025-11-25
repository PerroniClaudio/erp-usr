<x-layouts.app>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-4xl">{{ __('daily_travel.index_title') }}</h1>
        <div class="flex gap-2 justify-end">
            <button class="btn btn-primary" onclick="document.getElementById('export_nota_spese').showModal()">
                <x-lucide-file-text class="w-4 h-4" />
                <span class="ml-2">{{ __('daily_travel.export_nota_spese') }}</span>
            </button>
            <a class="btn btn-primary" href="{{ route('daily-travels.create') }}">
                {{ __('daily_travel.create_title') }}
            </a>
        </div>
    </div>

    <hr>

    @if (session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="card bg-base-300">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('daily_travel.travel_date') }}</th>
                            <th>{{ __('daily_travel.company_label') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dailyTravels as $travel)
                            <tr>
                                <td>{{ $travel->travel_date?->format('d/m/Y') }}</td>
                                <td>{{ $travel->company?->name }}</td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <a class="btn btn-sm btn-primary"
                                            href="{{ route('daily-travels.show', $travel) }}"
                                            aria-label="{{ __('daily_travel.view') }}">
                                            <x-lucide-pencil class="w-4 h-4" />
                                        </a>
                                        <form method="POST" action="{{ route('daily-travels.destroy', $travel) }}"
                                            onsubmit="return confirm('{{ __('daily_travel.delete_confirm') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-warning"
                                                aria-label="{{ __('daily_travel.delete') }}">
                                                <x-lucide-trash-2 class="w-4 h-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-base-content/70">
                                    {{ __('daily_travel.index_empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $dailyTravels->links() }}
            </div>
        </div>
    </div>

    <dialog id="export_nota_spese" class="modal">
        <div class="modal-box">
            <div class="flex flex-row-reverse items-end">
                <form method="dialog">
                    <button class="btn btn-ghost">
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </form>
            </div>
            <h1 class="text-3xl mb-4">{{ __('daily_travel.export_nota_spese') }}</h1>
            <hr>
            <form action="{{ route('daily-travels.pdf-batch') }}" method="GET">
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
                    {{ __('daily_travel.export_nota_spese') }}
                </button>

            </form>
        </div>
    </dialog>
</x-layouts.app>
