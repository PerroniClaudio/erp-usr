<x-layouts.app>

    @vite('resources/js/file-protocols.js')

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('files.protocols_page_title') }}</h1>
        <a href="{{ route('admin.protocols.create') }}" class="btn btn-primary"><x-lucide-plus
                class="w-4 h-4" />{{ __('files.protocols_new_button') }}</a>
    </div>
    <hr>

    <table class="table overflow-x-scroll">
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ __('files.protocols_table_header_name') }}</th>
                <th>{{ __('files.protocols_table_header_acronym') }}</th>
                <th>{{ __('files.protocols_table_header_counter') }}</th>
                <th>{{ __('files.protocols_table_header_year') }}</th>
                <th>{{ __('files.protocols_table_header_actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($protocols as $protocol)
                <tr>
                    <td>{{ $protocol->id }}</td>
                    <td>{{ $protocol->name }}</td>
                    <td>{{ strtoupper($protocol->acronym) }}</td>
                    <td>{{ $protocol->counter }}</td>
                    <td>{{ $protocol->counter_year ?? __('files.protocols_counter_year_missing') }}</td>
                    <td>
                        <a href="{{ route('admin.protocols.show', $protocol) }}" class="btn btn-sm btn-primary">
                            <x-lucide-pencil class="w-4 h-4" />
                        </a>
                        <button class="btn btn-sm btn-warning" data-delete-protocol-id="{{ $protocol->id }}">
                            <x-lucide-trash class="w-4 h-4" />
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <input type="hidden" id="deleteProtocolRouteTemplate" value="{{ route('admin.protocols.destroy', ':id') }}">

    <dialog id="deleteProtocolModal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">{{ __('files.protocols_delete_title') }}</h3>
            <p class="py-2 text-sm text-base-content/80">{{ __('files.protocols_delete_body') }}</p>
            <form method="POST" id="deleteProtocolForm" class="modal-action">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-ghost" data-close-delete-protocol-modal>{{ __('files.protocols_delete_cancel') }}</button>
                <button type="submit" class="btn btn-warning">{{ __('files.protocols_delete_confirm') }}</button>
            </form>
        </div>
    </dialog>

</x-layouts.app>
