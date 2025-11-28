<x-layouts.app>

    @vite('resources/js/file_explorer.js')

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('files.files_index_title') }}</h1>
    </div>
    <hr>

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
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
