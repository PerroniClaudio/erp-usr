<x-layouts.app>

    @vite('resources/js/file_explorer.js')

    <x-layouts.header :title="__('files.files_index_title')" class="flex-wrap gap-2">
        <x-slot:actions>
            <a class="btn btn-ghost" href="{{ route('admin.files.search') }}">
                <x-lucide-search class="w-4 h-4" />
                {{ __('files.files_search_button') }}
            </a>
        </x-slot:actions>
    </x-layouts.header>

    <div class="card bg-base-300">
        <div class="card-body">
            <table class="table">
                <thead>
                    <th class="w-1/2">{{ __('files.files_table_header_name') }}</th>
                    <th>{{ __('files.files_table_header_sector') }}</th>
                    <th>{{ __('files.files_table_header_type') }}</th>
                    <th>{{ __('files.files_table_header_size') }}</th>
                    <th>{{ __('files.files_table_header_uploaded_at') }}</th>
                    <th>{{ __('files.files_table_header_actions') }}</th>
                </thead>
                <tbody>
                    @php $currentUser = auth()->user(); @endphp
                    @if ($currentUser->hasRole('admin'))
                        <tr class="hover:bg-base-200 cursor-pointer" data-folder-hash="{{ base64_encode('/public') }}">
                            <td colspan="6">
                                <div class="flex items-center">
                                    <x-lucide-folder class="w-6 h-6 mr-2 text-yellow-400" />
                                    <span>{{ __('files.files_table_default_public_files') }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-base-200 cursor-pointer" data-folder-hash="{{ base64_encode('/personnel') }}">
                            <td colspan="6">
                                <div class="flex items-center">
                                    <x-lucide-folder class="w-6 h-6 mr-2 text-yellow-400" />
                                    <span>{{ __('files.files_table_default_private_files') }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-base-200 cursor-pointer"
                            data-folder-hash="{{ base64_encode('/attachments') }}">
                            <td colspan="6">
                                <div class="flex items-center">
                                    <x-lucide-folder class="w-6 h-6 mr-2 text-yellow-400" />
                                    <span>{{ __('files.files_table_default_attached_files') }}</span>
                                </div>
                            </td>
                        </tr>
                    @else
                        <tr class="hover:bg-base-200 cursor-pointer"
                            data-folder-hash="{{ base64_encode('/personnel/' . $currentUser->id) }}">
                            <td colspan="6">
                                <div class="flex items-center">
                                    <x-lucide-folder class="w-6 h-6 mr-2 text-yellow-400" />
                                    <span>{{ $currentUser->name }}</span>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
