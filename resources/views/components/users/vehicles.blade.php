@props([
    'user' => null,
])

<div class="card bg-base-300 max-w-full col-span-2">
    <div class="card-body">

        <div class="flex items-center justify-between">
            <h2 class="card-title">{{ __('personnel.user_vehicles') }}</h2>
            </h2>
            <a href="{{ route('users.add-vehicles', $user) }}" class="btn btn-primary">
                <x-lucide-plus class="h-4 w-4" />
            </a>
        </div>


        <hr>

        <table class="table w-full">
            <thead>
                <tr>
                    <th>{{ __('personnel.user_vehicles_plate_number') }}</th>
                    <th>{{ __('personnel.user_vehicles_model') }}</th>
                    <th>{{ __('personnel.user_vehicles_brand') }}</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody class="overflow-y-auto">
                @unless ($user->vehicles->count())
                    <tr>
                        <td colspan="5" class="text-center">
                            {{ __('personnel.user_vehicles_no_vehicles') }}
                        </td>
                    </tr>
                @endunless
                @foreach ($user->vehicles as $vehicle)
                    <tr>
                        <td>{{ $vehicle->pivot->plate_number }}</td>
                        <td>{{ $vehicle->model }}</td>
                        <td>{{ $vehicle->brand }}</td>

                        <td>
                            <a href="{{ route('users.vehicles.edit', [
                                'user' => $user->id,
                                'vehicle' => $vehicle->id,
                            ]) }}"
                                class="btn btn-sm btn-primary">
                                <x-lucide-pencil class="w-4 h-4" />
                            </a>

                            <form
                                action="{{ route('users.vehicles.destroy', [
                                    'user' => $user->id,
                                    'vehicle' => $vehicle->id,
                                ]) }}"
                                method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-sm btn-warning">
                                    <x-lucide-trash-2 class="w-4 h-4" />
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>
</div>
