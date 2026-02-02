<?php

namespace App\Support;

class MapboxAddressParser
{
    public static function coordinates(array $feature): ?array
    {
        $center = $feature['center'] ?? null;
        if (!is_array($center) || count($center) < 2) {
            return null;
        }

        return [
            'latitude' => (float) $center[1],
            'longitude' => (float) $center[0],
        ];
    }

    public static function displayName(array $feature): ?string
    {
        return $feature['place_name'] ?? null;
    }

    public static function addressDetails(array $feature): array
    {
        $street = $feature['text'] ?? null;
        $houseNumber = $feature['address'] ?? ($feature['properties']['address'] ?? null);
        $context = is_array($feature['context'] ?? null) ? $feature['context'] : [];

        $city = self::contextValue($context, ['place', 'locality']);
        $province = self::contextValue($context, ['region', 'district']);
        $postcode = self::contextValue($context, ['postcode']);

        return [
            'road' => $street,
            'house_number' => $houseNumber,
            'city' => $city,
            'town' => $city,
            'village' => $city,
            'county' => $province,
            'postcode' => $postcode,
        ];
    }

    private static function contextValue(array $context, array $types): ?string
    {
        foreach ($context as $item) {
            $id = $item['id'] ?? '';
            foreach ($types as $type) {
                if (str_starts_with($id, $type.'.')) {
                    return $item['text'] ?? null;
                }
            }
        }

        return null;
    }
}
