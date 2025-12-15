<x-layouts.app>
    <x-layouts.header :title="__('overtime_requests.your_requests')">
        <x-slot:actions>
            <a href="{{ route('overtime-requests.create') }}" class="btn btn-primary mb-3">
                {{ __('overtime_requests.new') }}
            </a>
        </x-slot:actions>
    </x-layouts.header>

    <div>
        <div id="calendar" class="max-w-full mb-6"></div>
    </div>

    @push('scripts')
        @vite('resources/js/overtime_requests.js')
    @endpush
</x-layouts.app>
