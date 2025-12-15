<x-layouts.app>
    <x-layouts.header :title="__('time_off_requests.time_off_requests')">
        <x-slot:actions>
            <a href="{{ route('time-off-requests.create') }}" class="btn btn-primary">
                {{ __('time_off_requests.new_request') }}
            </a>
        </x-slot:actions>
    </x-layouts.header>

    <div>
        <div id="calendar" class="max-w-full"></div>
    </div>

    @push('scripts')
        @vite('resources/js/time_off_requests.js')
    @endpush
</x-layouts.app>
