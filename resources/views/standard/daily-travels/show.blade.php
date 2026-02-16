<x-layouts.app>
    <x-layouts.header :title="__('daily_travel.show_title')">
        <x-slot:actions>
            <a class="btn btn-primary" href="{{ route('daily-travels.index') }}">{{ __('daily_travel.back_to_list') }}</a>
            <form method="POST" action="{{ route('daily-travels.destroy', $dailyTravel) }}"
                onsubmit="return confirm('{{ __('daily_travel.delete_confirm') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error" aria-label="{{ __('daily_travel.delete') }}">
                    <x-lucide-trash-2 class="w-4 h-4" />
                </button>
            </form>
        </x-slot>
    </x-layouts.header>

    @if (session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error mb-4">
            {{ __('daily_travel.additional_expense_validation_error') }}
        </div>
    @endif

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
                        <p class="text-xs uppercase text-base-content/60">{{ __('daily_travel.status_label') }}</p>
                        <p class="font-semibold">
                            {{ __('daily_travel.status_' . $dailyTravel->approvalStatus()) }}
                        </p>
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
                                <th>{{ __('daily_travel.route_title') }}</th>
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
                                    <td>{{ $step->name }}</td>
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
                    data-steps='@json($mapSteps)' data-mapbox-token="{{ $mapboxAccessToken }}">
                    <p class="p-4 text-sm text-gray-500">{{ __('daily_travel.map_placeholder') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-200 mt-4">
        <div class="card-body space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="font-semibold">{{ __('daily_travel.additional_expenses_title') }}</h4>
                <button class="btn btn-primary btn-sm" onclick="document.getElementById('additional_expense_modal').showModal()">
                    {{ __('daily_travel.additional_expense_add') }}
                </button>
            </div>
            <hr>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>{{ __('daily_travel.additional_expense_description') }}</th>
                            <th>{{ __('daily_travel.additional_expense_amount') }}</th>
                            <th>{{ __('daily_travel.additional_expense_occurred_at') }}</th>
                            <th>{{ __('daily_travel.additional_expense_uploaded_by') }}</th>
                            <th>{{ __('daily_travel.additional_expense_file') }}</th>
                            <th>{{ __('daily_travel.expense_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($additionalExpenses as $expense)
                            <tr>
                                <td>{{ $expense->description }}</td>
                                <td>€ {{ number_format((float) $expense->amount, 2, ',', '.') }}</td>
                                <td>{{ $expense->occurred_at?->format('d/m/Y H:i') }}</td>
                                <td>{{ $expense->uploader?->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('daily-travels.additional-expenses.download', [$dailyTravel, $expense]) }}"
                                        class="btn btn-sm btn-outline">
                                        {{ __('daily_travel.additional_expense_download') }}
                                    </a>
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <button class="btn btn-sm btn-primary"
                                            aria-label="{{ __('daily_travel.additional_expense_edit') }}"
                                            onclick="document.getElementById('edit_additional_expense_modal_{{ $expense->id }}').showModal()">
                                            <x-lucide-pencil class="w-4 h-4" />
                                        </button>
                                        <form method="POST"
                                            action="{{ route('daily-travels.additional-expenses.destroy', [$dailyTravel, $expense]) }}"
                                            onsubmit="return confirm('{{ __('daily_travel.additional_expense_delete_confirm') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-error"
                                                aria-label="{{ __('daily_travel.delete') }}">
                                                <x-lucide-trash-2 class="w-4 h-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-sm text-base-content/70">
                                    {{ __('daily_travel.additional_expenses_empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <dialog id="additional_expense_modal" class="modal">
        <div class="modal-box">
            <div class="flex flex-row-reverse items-end">
                <form method="dialog">
                    <button class="btn btn-ghost">
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </form>
            </div>

            <h3 class="text-xl font-semibold mb-4">{{ __('daily_travel.additional_expense_add') }}</h3>
            <form method="POST" action="{{ route('daily-travels.additional-expenses.store', $dailyTravel) }}"
                enctype="multipart/form-data" class="grid gap-3">
                @csrf
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('daily_travel.additional_expense_description') }}</legend>
                    <input type="text" name="description" class="input input-bordered w-full"
                        value="{{ old('description') }}" maxlength="255" required />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('daily_travel.additional_expense_amount') }}</legend>
                    <input type="number" name="amount" class="input input-bordered w-full" value="{{ old('amount') }}"
                        step="0.01" min="0" required />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('daily_travel.additional_expense_occurred_at') }}</legend>
                    <input type="datetime-local" name="occurred_at" class="input input-bordered w-full"
                        value="{{ old('occurred_at') }}" required />
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('daily_travel.additional_expense_file') }}</legend>
                    <input type="file" name="proof_file" class="file-input file-input-bordered w-full"
                        accept="image/*,.pdf" required />
                </fieldset>

                <button type="submit" class="btn btn-primary">
                    {{ __('daily_travel.additional_expense_add') }}
                </button>
            </form>
        </div>
    </dialog>

    @foreach ($additionalExpenses as $expense)
        <dialog id="edit_additional_expense_modal_{{ $expense->id }}" class="modal">
            <div class="modal-box">
                <div class="flex flex-row-reverse items-end">
                    <form method="dialog">
                        <button class="btn btn-ghost">
                            <x-lucide-x class="w-4 h-4" />
                        </button>
                    </form>
                </div>

                <h3 class="text-xl font-semibold mb-4">{{ __('daily_travel.additional_expense_edit') }}</h3>
                <form method="POST"
                    action="{{ route('daily-travels.additional-expenses.update', [$dailyTravel, $expense]) }}"
                    enctype="multipart/form-data" class="grid gap-3">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="editing_additional_expense_id" value="{{ $expense->id }}">

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('daily_travel.additional_expense_description') }}</legend>
                        <input type="text" name="description" class="input input-bordered w-full"
                            value="{{ old('editing_additional_expense_id') == $expense->id ? old('description') : $expense->description }}"
                            maxlength="255" required />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('daily_travel.additional_expense_amount') }}</legend>
                        <input type="number" name="amount" class="input input-bordered w-full"
                            value="{{ old('editing_additional_expense_id') == $expense->id ? old('amount') : $expense->amount }}"
                            step="0.01" min="0" required />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('daily_travel.additional_expense_occurred_at') }}</legend>
                        <input type="datetime-local" name="occurred_at" class="input input-bordered w-full"
                            value="{{ old('editing_additional_expense_id') == $expense->id
                                ? old('occurred_at')
                                : optional($expense->occurred_at)->format('Y-m-d\TH:i') }}"
                            required />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('daily_travel.additional_expense_file') }}</legend>
                        <input type="file" name="proof_file" class="file-input file-input-bordered w-full"
                            accept="image/*,.pdf" />
                    </fieldset>

                    <button type="submit" class="btn btn-primary">
                        {{ __('daily_travel.additional_expense_update') }}
                    </button>
                </form>
            </div>
        </dialog>
    @endforeach

    @push('scripts')
        @vite('resources/js/daily-travel-structure.js')
        @if ($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const editingId = @json(old('editing_additional_expense_id'));
                    if (editingId) {
                        document.getElementById(`edit_additional_expense_modal_${editingId}`)?.showModal();
                        return;
                    }
                    document.getElementById('additional_expense_modal')?.showModal();
                });
            </script>
        @endif
    @endpush
</x-layouts.app>
