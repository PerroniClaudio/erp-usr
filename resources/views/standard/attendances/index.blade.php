<x-layouts.app>

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('attendances.attendances') }}</h1>
        <a href="{{ route('attendances.create') }}" class="btn btn-primary">
            {{ __('attendances.new_attendance') }}
        </a>
    </div>

    <hr>

    <div>
        <div id="calendar" class="max-w-full"></div>
    </div>

    @push('scripts')
        @vite('resources/js/attedances.js')
    @endpush

</x-layouts.app>
