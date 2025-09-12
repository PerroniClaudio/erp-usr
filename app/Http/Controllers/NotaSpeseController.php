<?php

namespace App\Http\Controllers;

use App\Models\BusinessTrip;
use App\Models\BusinessTripExpense;
use App\Models\BusinessTripTransfer;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http as HttpClient;
use Illuminate\Support\Facades\Log;

class NotaSpeseController extends Controller
{
    public function exportMonthly(Request $request, $userId)
    {
        $fields = $request->validate([
            'month' => 'required',
            'year' => 'required|integer|min:1900',
        ]);

        $start_of_month = date('Y-m-d', strtotime($fields['year'].'-'.$fields['month'].'-01'));
        $end_of_month = date('Y-m-t', strtotime($fields['year'].'-'.$fields['month'].'-01'));

        $businessTrips = BusinessTrip::where('user_id', $userId)
            ->whereBetween('date_from', [$start_of_month, $end_of_month])
            ->with(['user'])
            ->orderBy('date_from', 'asc')
            ->get();

        if ($businessTrips->isEmpty()) {
            return redirect()->back()->with('error', 'Nessuna trasferta trovata per il mese e anno specificati.');
        }

        $allTripsData = [];
        $user_vehicle = null;

        foreach ($businessTrips as $businessTrip) {
            $transfers = BusinessTripTransfer::where('business_trip_id', $businessTrip->id)->with(['company'])->get();

            if ($user_vehicle == null) {
                $vehicle_id = $transfers->count() > 0 ? $transfers[0]->vehicle_id : null;
                $user_vehicles = $businessTrip->user->vehicles;

                foreach ($user_vehicles as $vehicle) {
                    if ($vehicle->id == $vehicle_id) {
                        $user_vehicle = $vehicle;
                        break;
                    }
                }
            }

            $pairs = [];
            for ($i = 0; $i < count($transfers) - 1; $i++) {
                $pairs[] = [
                    'from' => $transfers[$i],
                    'to' => $transfers[$i + 1],
                ];
            }

            $transferPairs = [];
            foreach ($pairs as $pair) {
                $distance = $this->routeDistanceGoogle(
                    $pair['from']->latitude,
                    $pair['from']->longitude,
                    $pair['to']->latitude,
                    $pair['to']->longitude
                );

                $transferPairs[] = [
                    'from' => $pair['from'],
                    'to' => $pair['to'],
                    'azienda' => $pair['to']->company->name,
                    'ekm' => round($pair['from']->vehicle->price_per_km, 2),
                    'distance' => round($distance, 2),
                    'total' => round($distance * $pair['from']->vehicle->price_per_km, 2),
                ];
            }

            $allTripsData[] = [
                'businessTrip' => $businessTrip,
                'expenses' => BusinessTripExpense::where('business_trip_id', $businessTrip->id)->with(['company'])->get(),
                'transfers' => $transferPairs,
                'user_vehicle' => $user_vehicle,
            ];
        }

        $pdf = PDF::loadView('cedolini.business_trips_batch', [
            'allTripsData' => $allTripsData,
            'month' => $fields['month'],
            'year' => $fields['year'],
            'document_date' => date('Y-m-d'),
            'user_vehicle' => $user_vehicle,
            'user' => $businessTrips->first()->user,
        ]);

        return $pdf->download('nota_spese_'.$fields['year'].'_'.str_pad($fields['month'], 2, '0', STR_PAD_LEFT).'.pdf');
    }

    // Copia del metodo routeDistanceGoogle da BusinessTripController
    private function routeDistanceGoogle($lat1, $lon1, $lat2, $lon2)
    {
        $apiKey = config('services.google_maps.api_key');
        if (! $apiKey) {
            Log::error('Google Maps API key mancante.');

            return null;
        }

        $url = 'https://routes.googleapis.com/directions/v2:computeRoutes';
        $body = [
            'origin' => [
                'location' => [
                    'latLng' => [
                        'latitude' => (float) $lat1,
                        'longitude' => (float) $lon1,
                    ],
                ],
            ],
            'destination' => [
                'location' => [
                    'latLng' => [
                        'latitude' => (float) $lat2,
                        'longitude' => (float) $lon2,
                    ],
                ],
            ],
            'travelMode' => 'DRIVE',
            'routingPreference' => 'TRAFFIC_UNAWARE',
            'units' => 'METRIC',
        ];

        try {
            $response = HttpClient::withHeaders([
                'Content-Type' => 'application/json',
                'X-Goog-Api-Key' => $apiKey,
                'X-Goog-FieldMask' => 'routes.distanceMeters',
            ])->post($url, $body);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['routes'][0]['distanceMeters'])) {
                    $distanceKm = $data['routes'][0]['distanceMeters'] / 1000;

                    return round($distanceKm, 2);
                } else {
                    Log::error('Risposta Routes API senza distanza valida: '.json_encode($data));
                }
            } else {
                Log::error('Errore Google Routes API: '.$response->body());
            }
        } catch (\Exception $e) {
            Log::error('Eccezione Google Routes API: '.$e->getMessage());
        }

        return null;
    }
}
