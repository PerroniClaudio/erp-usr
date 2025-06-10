<x-layouts.app>
    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('overtime_requests.your_requests') }}</h1>
        <a href="{{ route('overtime-requests.create') }}"
            class="btn btn-primary mb-3">{{ __('overtime_requests.new') }}</a>
    </div>

    <hr>

    <div>
        <div id="calendar" class="max-w-full mb-6"></div>
    </div>

    @push('scripts')
        @vite('resources/js/overtime_requests.js')
    @endpush
</x-layouts.app>
