<x-layouts.app>
    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('personnel.users') }}</h1>
    </div>

    <hr>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>{{ __('personnel.users_name') }}</th>
                    <th>{{ __('personnel.users_email') }}</th>
                    <th>{{ __('personnel.users_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>


                    </tr>
                @endforeach
            </tbody>
        </table>

</x-layouts.app>
