<x-layouts.app>

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('personnel.groups') }}</h1>
        <a href="{{ route('groups.create') }}" class="btn btn-primary">
            {{ __('personnel.groups_new_group') }}
        </a>
    </div>

    <hr>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>{{ __('personnel.groups_name') }}</th>
                    <th>{{ __('personnel.groups_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groups as $group)
                    <tr>
                        <td>{{ $group->id }}</td>
                        <td>{{ $group->name }}</td>

                        <td>
                            <a href="{{ route('groups.edit', $group) }}" class="btn btn-primary">

                                <x-lucide-pencil class="h-4 w-4" />
                            </a>
                            <form action="{{ route('groups.destroy', $group) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-warning">

                                    <x-lucide-trash-2 class="h-4 w-4" />
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $companies->links() }}
    </div>

</x-layouts.app>
