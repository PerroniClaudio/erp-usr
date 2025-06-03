<x-layouts.email>
    @slot('header')
        <h3>Presenze non registrate</h3>
    @endslot

    <p>Nella giornata odierna i seguenti utenti non hanno registrato le presenze:</p>
    <table class="table" style="margin-top:24px;">
        <thead>
            <tr>
                <th>Nome</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user['user']['name'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-layouts.email>
