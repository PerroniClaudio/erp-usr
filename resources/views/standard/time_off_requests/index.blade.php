<x-layouts.app>
    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('time_off_requests.time_off_requests') }}</h1>
        <a href="{{ route('time-off-requests.create') }}" class="btn btn-primary">
            {{ __('time_off_requests.new_request') }}
        </a>
    </div>

    <hr>

    <div>
        <div id="calendar" class="max-w-full"></div>
    </div>

    @push('scripts')
        @vite('resources/js/time_off_requests.js')
    @endpush
</x-layouts.app>
