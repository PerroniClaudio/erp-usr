<x-layouts.app>

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('personnel.companies') }}</h1>
        <a href="{{ route('companies.create') }}" class="btn btn-primary">
            {{ __('personnel.companies_new_company') }}
        </a>
    </div>

    <hr>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>{{ __('personnel.companies_name') }}</th>
                    <th>{{ __('personnel.companies_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($companies as $company)
                    <tr>
                        <td>{{ $company->id }}</td>
                        <td>{{ $company->name }}</td>

                        <td>
                            <a href="{{ route('companies.edit', $company) }}" class="btn btn-primary">

                                <x-lucide-pencil class="h-4 w-4" />
                            </a>
                            <form action="{{ route('companies.destroy', $company) }}" method="POST" class="inline">
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
