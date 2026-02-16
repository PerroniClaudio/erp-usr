<x-layouts.app>
    <x-layouts.header :title="__('daily_travel.admin_review_title')">
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

    <div class="card bg-base-200 mb-6">
        <div class="card-body space-y-3">
            <h3 class="card-title m-0">{{ __('daily_travel.additional_expenses_title') }}</h3>
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
                                    <a href="{{ route('admin.daily-travels.additional-expenses.download', [$dailyTravel, $expense]) }}"
                                        class="btn btn-sm btn-outline">
                                        {{ __('daily_travel.additional_expense_download') }}
                                    </a>
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <button class="btn btn-sm btn-primary"
                                            aria-label="{{ __('daily_travel.additional_expense_edit') }}"
                                            onclick="document.getElementById('admin_edit_additional_expense_modal_{{ $expense->id }}').showModal()">
                                            <x-lucide-pencil class="w-4 h-4" />
                                        </button>
                                        <form method="POST"
                                            action="{{ route('admin.daily-travels.additional-expenses.destroy', [$dailyTravel, $expense]) }}"
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

    @foreach ($additionalExpenses as $expense)
        <dialog id="admin_edit_additional_expense_modal_{{ $expense->id }}" class="modal">
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
                    action="{{ route('admin.daily-travels.additional-expenses.update', [$dailyTravel, $expense]) }}"
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
    @push('scripts')
        @if ($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const editingId = @json(old('editing_additional_expense_id'));
                    if (editingId) {
                        document.getElementById(`admin_edit_additional_expense_modal_${editingId}`)?.showModal();
                    }
                });
            </script>
        @endif
    @endpush
</x-layouts.app>
