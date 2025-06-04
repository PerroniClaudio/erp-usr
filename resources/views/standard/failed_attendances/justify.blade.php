<x-layouts.app>
    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('attendances.failed_attendance') }}</h1>
        <a class="btn btn-primary" onclick="document.getElementById('submit-button').click()">
            {{ __('attendances.submit_justification') }}
        </a>
    </div>
    <hr>

    <form
        action="{{ route('failed-attendances.send-justification', [
            'failed_attendance' => $failedAttendance->id,
        ]) }}"
        method="POST" class="card bg-base-300">
        @csrf
        <div class="card-body">
            <p>{{ __('attendances.failed_explanation', [
                'date' => \Carbon\Carbon::parse($failedAttendance->date)->format('d/m/Y'),
            ]) }}
            </p>

            <div class="flex flex-col gap-2 w-1/3">

                <div class="mb-4">
                    <label for="reason"
                        class="block text-sm font-medium mb-1">{{ __('attendances.justification') }}</label>
                    <textarea id="reason" name="reason" class="textarea textarea-bordered w-full" rows="4" required>{{ $failedAttendance->reason }}</textarea>
                </div>

                <div class="mb-4">
                    <label for="type" class="block text-sm font-medium mb-1">{{ __('attendances.type') }}</label>
                    <select id="type" name="type" class="select select-bordered w-full" required>
                        <option disabled selected>Scegli</option>
                        <option value="0">ROL</option>
                        <option value="1">Ferie</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="lacking_hours"
                        class="block text-sm font-medium mb-1">{{ __('attendances.lacking_hours') }}</label>
                    <input id="lacking_hours" name="lacking_hours" type="text" class="input input-bordered w-full"
                        value="{{ $failedAttendance->requested_hours }}" disabled>
                </div>

            </div>


        </div>

        <button type="submit" id="submit-button" class="hidden"></button>
    </form>
</x-layouts.app>
