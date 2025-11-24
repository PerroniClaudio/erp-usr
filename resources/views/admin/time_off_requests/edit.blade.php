<x-layouts.app>

    <input type="hidden" name="batch_id" id="batch_id" value="{{ $batch_id }}">

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('time_off_requests.edit_request') }}</h1>
    </div>

    <hr>

    <!-- Toast notification container -->
    <div id="toast-container" class="toast toast-top toast-end hidden">
        <div class="alert alert-success">
            <x-lucide-check class="w-4 h-4" />
            <span id="toast-message">{{ __('time_off_requests.type_updated_successfully') }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">

        <div class="flex flex-col gap-4 h-fit">
            <div class="card bg-base-300 ">
                <div class="card-body">
                    <h3 class="card-title">{{ __('time_off_requests.request_details') }}</h3>
                    <hr>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('time_off_requests.requester') }}</legend>
                        <input type="text" class="input w-full" value="{{ $requests->first()->user->name }}"
                            disabled />
                    </fieldset>
                </div>
            </div>

            <div class="card bg-base-300 ">
                <div class="card-body">
                    <h3 class="card-title">{{ __('time_off_requests.selected_days') }}</h3>
                    <hr>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('time_off_requests.start_date') }}</legend>
                        <input type="date" id="date_from" name="date_from" class="input w-full"
                            value="{{ \Carbon\Carbon::parse($requests->first()->date_from)->format('Y-m-d') }}"
                            disabled />
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('time_off_requests.end_date') }}</legend>
                        <input type="date" id="date_to" name="date_to" class="input w-full"
                            value="{{ \Carbon\Carbon::parse($requests->last()->date_to)->format('Y-m-d') }}"
                            disabled />
                    </fieldset>
                </div>
            </div>

            @php
                $requestStatus = $requests->first()->status;
            @endphp

            @if ($requestStatus == 0)
                <div class="btn btn-primary" onclick="document.getElementById('approve').showModal()">
                    {{ __('time_off_requests.approve') }}
                </div>
                <div class="btn btn-secondary" onclick="document.getElementById('deny').showModal()">
                    {{ __('time_off_requests.deny') }}
                </div>

                <dialog id="approve" class="modal">
                    <div class="modal-box">
                        <div class="flex flex-row-reverse items-end">
                            <form method="dialog">
                                <!-- if there is a button in form, it will close the modal -->
                                <button class="btn btn-ghost">
                                    <x-lucide-x class="w-4 h-4" />
                                </button>
                            </form>
                        </div>
                        <h3 class="text-lg font-bold"> {{ __('time_off_requests.approve_request_title') }}</h3>
                        <p class="py-4">
                            {{ __('time_off_requests.approve_request_text') }}
                        </p>

                        <div class="flex flex-row-reverse">
                            <form method="POST"
                                action="{{ route('admin.time-off.approve', ['time_off_request' => $requests->first()->id]) }}">
                                @csrf

                                <button type="submit" class="btn btn-primary">
                                    {{ __('time_off_requests.approve') }}
                                </button>
                            </form>
                        </div>

                    </div>
                </dialog>
                <dialog id="deny" class="modal">
                    <div class="modal-box">
                        <div class="flex flex-row-reverse items-end">
                            <form method="dialog">
                                <!-- if there is a button in form, it will close the modal -->
                                <button class="btn btn-ghost">
                                    <x-lucide-x class="w-4 h-4" />
                                </button>
                            </form>
                        </div>
                        <h3 class="text-lg font-bold"> {{ __('time_off_requests.deny_request_title') }}</h3>
                        <p class="py-4">
                            {{ __('time_off_requests.deny_request_text') }}
                        </p>

                        <form method="POST"
                            action="{{ route('admin.time-off.deny', ['time_off_request' => $requests->first()->id]) }}">
                            @csrf

                            <fieldset class="fieldset mb-4">
                                <legend class="fieldset-legend">{{ __('time_off_requests.deny_reason') }}</legend>
                                <textarea name="denial_reason" class="textarea textarea-bordered w-full"
                                    placeholder="{{ __('time_off_requests.deny_reason_placeholder') }}" required></textarea>
                            </fieldset>

                            <div class="flex flex-row-reverse">
                                <button type="submit" class="btn btn-secondary">
                                    {{ __('time_off_requests.deny') }}
                                </button>
                            </div>
                        </form>

                    </div>
                </dialog>
            @else
                @switch($requestStatus)
                    @case(2)
                        <div role="alert" class="alert alert-success">
                            <x-lucide-check class="w-6 h-6" />
                            <span>{{ __('time_off_requests.been_approved') }}</span>
                        </div>
                    @break

                    @case(3)
                        <div role="alert" class="alert alert-warning">
                            <x-lucide-alert-triangle class="w-6 h-6" />
                            <span>{{ __('time_off_requests.been_denied') }}</span>
                        </div>
                    @break
                @endswitch
            @endif

            <div class="btn btn-warning" onclick="document.getElementById('delete').showModal()">
                {{ __('time_off_requests.delete') }}
            </div>

            <dialog id="delete" class="modal">
                <div class="modal-box">
                    <div class="flex flex-row-reverse items-end">
                        <form method="dialog">
                            <!-- if there is a button in form, it will close the modal -->
                            <button class="btn btn-ghost">
                                <x-lucide-x class="w-4 h-4" />
                            </button>
                        </form>
                    </div>
                    <h3 class="text-lg font-bold"> {{ __('time_off_requests.delete_request_title') }}</h3>
                    <p class="py-4">
                        {{ __('time_off_requests.delete_request_text') }}
                    </p>

                    <div class="flex flex-row-reverse">
                        <form method="POST"
                            action="{{ route('admin.time-off.delete', ['time_off_request' => $requests->first()->id]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-warning">
                                {{ __('time_off_requests.delete') }}
                            </button>
                        </form>
                    </div>

                </div>
            </dialog>


        </div>

        <div class="col-span-1 lg:col-span-3">
            <div class="card bg-base-300 " id="days-card">
                <div class="card-body">
                    <h3 class="card-title">{{ __('time_off_requests.new_request_days') }}</h3>
                    <hr>

                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                                <tr>
                                    <th>{{ __('time_off_requests.new_request_type') }}</th>
                                    <th>{{ __('time_off_requests.new_request_day') }}</th>
                                    <th>{{ __('time_off_requests.new_request_start_time') }}</th>
                                    <th>{{ __('time_off_requests.new_request_end_time') }}</th>
                                    <th>{{ __('time_off_requests.new_request_total_hours') }}</th>
                                </tr>
                            </thead>
                            <tbody id="days-table-body">
                                <!-- Rows will be added here dynamically -->
                                @foreach ($requests as $request)
                                    @continue($request->isInvalidDate())
                                    <tr class="day-row" data-key="{{ $request->id }}">
                                        <td>
                                            <fieldset class="fieldset">
                                                @if ($request->status == 0)
                                                    <select class="select type-select" name="type"
                                                        data-request-id="{{ $request->id }}"
                                                        onchange="updateRequestType({{ $request->id }}, this.value)">
                                                        <option value="1"
                                                            {{ $request->type->id == 1 ? 'selected' : '' }}>
                                                            {{ __('time_off_requests.vacation') }}
                                                        </option>
                                                        <option value="2"
                                                            {{ $request->type->id == 2 ? 'selected' : '' }}>
                                                            {{ __('time_off_requests.rol') }}
                                                        </option>
                                                    </select>
                                                @else
                                                    <select class="select" name="type" disabled>
                                                        <option value="{{ $request->type->id }}" selected>
                                                            {{ $request->type->name }}
                                                        </option>
                                                    </select>
                                                @endif
                                            </fieldset>
                                        </td>
                                        <td>
                                            <fieldset class="fieldset">
                                                <input type="date" name="day" class="input"
                                                    value="{{ \Carbon\Carbon::parse($request->date_from)->format('Y-m-d') }}"
                                                    disabled />
                                            </fieldset>
                                        </td>
                                        <td>
                                            <fieldset class="fieldset">
                                                <input type="time" name="start_time" class="input"
                                                    value="{{ \Carbon\Carbon::parse($request->date_from)->format('H:i') }}"
                                                    disabled />
                                            </fieldset>
                                        </td>
                                        <td>
                                            <fieldset class="fieldset">
                                                <input type="time" name="end_time" class="input"
                                                    value="{{ \Carbon\Carbon::parse($request->date_to)->format('H:i') }}"
                                                    disabled />
                                            </fieldset>
                                        </td>
                                        <td width="5%">
                                            <fieldset class="fieldset">
                                                <input type="number" name="total_hours" class="input"
                                                    value="{{ number_format(\Carbon\Carbon::parse($request->date_from)->diffInMinutes(\Carbon\Carbon::parse($request->date_to)) / 60, 2) }}"
                                                    disabled />
                                            </fieldset>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function showToast() {
            const toast = document.getElementById('toast-container');
            toast.classList.remove('hidden');

            // Hide after 3 seconds
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }

        function updateRequestType(requestId, typeId) {
            const select = document.querySelector(`select[data-request-id="${requestId}"]`);
            const originalClass = select.className;

            // Show loading state
            select.disabled = true;
            select.className = originalClass + ' loading';

            fetch(`/admin/time-off-requests/${requestId}/update-single-type`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        time_off_type_id: typeId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    select.disabled = false;
                    select.className = originalClass;

                    if (data.success) {
                        // Show success feedback
                        select.className = originalClass + ' border-green-500';
                        showToast();

                        // Reset visual feedback after 3 seconds
                        setTimeout(() => {
                            select.className = originalClass;
                        }, 3000);
                    } else {
                        // Show error feedback
                        select.className = originalClass + ' border-red-500';
                        alert('{{ __('time_off_requests.update_error') }}');

                        setTimeout(() => {
                            select.className = originalClass;
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    select.disabled = false;
                    select.className = originalClass + ' border-red-500';
                    alert('{{ __('time_off_requests.update_error') }}');

                    setTimeout(() => {
                        select.className = originalClass;
                    }, 3000);
                });
        }
    </script>


</x-layouts.app>
