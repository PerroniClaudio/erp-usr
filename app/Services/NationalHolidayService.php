<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class NationalHolidayService
{
    private const FIXED_HOLIDAYS = [
        '01-01' => 'Capodanno',
        '01-06' => 'Epifania',
        '04-25' => 'Festa della Liberazione',
        '05-01' => 'Festa dei Lavoratori',
        '06-02' => 'Festa della Repubblica',
        '08-15' => 'Ferragosto',
        '11-01' => 'Ognissanti',
        '12-08' => "Immacolata Concezione",
        '12-25' => 'Natale',
        '12-26' => 'Santo Stefano',
    ];

    /**
     * @return \Illuminate\Support\Collection<int, array{date: string, label: string}>
     */
    public function getHolidaysBetween(Carbon $start, Carbon $end): Collection
    {
        $start = $start->copy()->startOfDay();
        $end = $end->copy()->endOfDay();

        $years = range($start->year, $end->year);
        $holidays = collect();

        foreach ($years as $year) {
            $holidays = $holidays->merge($this->getHolidaysForYear($year));
        }

        return $holidays
            ->filter(function (array $holiday) use ($start, $end) {
                $date = Carbon::parse($holiday['date'])->startOfDay();
                return $date->between($start, $end);
            })
            ->values();
    }

    public function isHoliday(Carbon $date): bool
    {
        return $this->getHolidaysBetween(
            $date->copy()->startOfDay(),
            $date->copy()->endOfDay()
        )->isNotEmpty();
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{date: string, label: string}>
     */
    private function getHolidaysForYear(int $year): Collection
    {
        $fixed = collect(self::FIXED_HOLIDAYS)->map(function ($label, $monthDay) use ($year) {
            return [
                'date' => Carbon::createFromFormat('Y-m-d', sprintf('%d-%s', $year, $monthDay))->toDateString(),
                'label' => $label,
            ];
        });

        $movable = collect($this->getMoveableHolidaysForYear($year))->map(function ($item) {
            return [
                'date' => $item['date']->toDateString(),
                'label' => $item['label'],
            ];
        });

        return $fixed->merge($movable)->values();
    }

    /**
     * @return array<int, array{date: \Carbon\Carbon, label: string}>
     */
    private function getMoveableHolidaysForYear(int $year): array
    {
        $easter = Carbon::createFromTimestamp(easter_date($year))->startOfDay();
        $easterMonday = $easter->copy()->addDay();

        return [
            ['date' => $easter, 'label' => 'Pasqua'],
            ['date' => $easterMonday, 'label' => "Lunedi dell'Angelo"],
        ];
    }
}
