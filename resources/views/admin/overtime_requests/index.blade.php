<x-layouts.app>
    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('overtime_requests.admin_list') }}</h1>
        <a href="{{ route('admin.overtime-requests.create') }}" class="btn btn-primary"><x-lucide-plus
                class="w-4 h-4" />{{ __('overtime_requests.new_admin') }}</a>
    </div>
    <hr>
    <div class="card bg-base-300">
        <div class="card-body">
            <div class="card-title">Filtra</div>
            <hr>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('overtime_requests.company') }}</legend>
                    <select id="company_filter" class="select w-full">
                        <option value="">Tutte le aziende</option>
                        @foreach ($requests->pluck('company')->unique('id') as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('overtime_requests.user') }}</legend>
                    <select id="user_filter" class="select w-full">
                        <option value="">Tutti gli utenti</option>
                        @foreach ($requests->pluck('user')->unique('id') as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('overtime_requests.type') }}</legend>
                    <select id="type_filter" class="select w-full">
                        <option value="">Tutti i tipi</option>
                        @foreach ($requests->pluck('overtimeType')->unique('id') as $type)
                            @if ($type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </fieldset>
            </div>
        </div>
    </div>
    <div>
        <div id="calendar" class="max-w-full"></div>
    </div>

    @push('scripts')
        @vite('resources/js/overtime_requests_admin.js')
    @endpush
</x-layouts.app>
