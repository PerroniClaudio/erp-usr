<x-layouts.app>

    @vite('resources/js/file-sectors.js')

    <x-layouts.header :title="__('files.sectors_page_title')">
        <x-slot:actions>
            <a href="{{ route('admin.sectors.create') }}" class="btn btn-primary">
                <x-lucide-plus class="w-4 h-4" />{{ __('files.sectors_new_button') }}
            </a>
        </x-slot:actions>
    </x-layouts.header>

    <table class="table overflow-x-scroll">
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ __('files.sectors_table_header_name') }}</th>
                <th>{{ __('files.sectors_table_header_acronym') }}</th>
                <th>{{ __('files.sectors_table_header_color') }}</th>
                <th>{{ __('files.sectors_table_header_actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sectors as $sector)
                <tr>
                    <td>{{ $sector->id }}</td>
                    <td>{{ $sector->name }}</td>
                    <td>{{ $sector->acronym }}</td>
                    <td>
                        <div class="w-6 h-6 rounded-full border border-base-content/20"
                            style="background-color: {{ $sector->color }};">
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('admin.sectors.show', $sector) }}" class="btn btn-sm btn-primary">
                            <x-lucide-pencil class="w-4 h-4" />
                        </a>
                        <button class="btn btn-sm btn-warning" data-delete-sector-id="{{ $sector->id }}">
                            <x-lucide-trash class="w-4 h-4" />
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <input type="hidden" id="deleteSectorRouteTemplate" value="{{ route('admin.sectors.destroy', ':id') }}">

    <dialog id="deleteSectorModal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">{{ __('files.sectors_delete_title') }}</h3>
            <p class="py-2 text-sm text-base-content/80">{{ __('files.sectors_delete_body') }}</p>
            <form method="POST" id="deleteSectorForm" class="modal-action">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-ghost" data-close-delete-modal>{{ __('files.sectors_delete_cancel') }}</button>
                <button type="submit" class="btn btn-warning">{{ __('files.sectors_delete_confirm') }}</button>
            </form>
        </div>
    </dialog>

</x-layouts.app>
