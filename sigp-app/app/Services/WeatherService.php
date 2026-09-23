<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    public static function forecastForDate(string $location, $date): ?array
    {
        $baseUrl = rtrim(env('WEATHERAPI_BASE_URL', 'https://api.weatherapi.com/v1'), '/');
        $key = env('WEATHERAPI_KEY');
        if (!$key || !$location || !$date) return null;

        $target = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        $res = Http::timeout(10)->get($baseUrl . '/forecast.json', [
            'key' => $key,
            'q' => $location,
            'days' => 10,
            'aqi' => 'no',
            'alerts' => 'no',
        ]);
        if (!$res->ok()) return null;

        $data = $res->json();
        $days = $data['forecast']['forecastday'] ?? [];
        foreach ($days as $day) {
            if (($day['date'] ?? null) === $target) {
                $d = $day['day'] ?? [];
                $c = $d['condition'] ?? [];
                return [
                    'date' => $day['date'] ?? null,
                    'condition_text' => $c['text'] ?? null,
                    'condition_icon' => $c['icon'] ?? null,
                    'maxtemp_c' => $d['maxtemp_c'] ?? null,
                    'mintemp_c' => $d['mintemp_c'] ?? null,
                    'avgtemp_c' => $d['avgtemp_c'] ?? null,
                    'maxwind_kph' => $d['maxwind_kph'] ?? null,
                    'daily_will_it_rain' => $d['daily_will_it_rain'] ?? null,
                    'daily_chance_of_rain' => $d['daily_chance_of_rain'] ?? null,
                ];
            }
        }
        return null;
    }

    public static function forecastEtaWithMarine(string $location, Carbon $eta): ?array
    {
        $baseUrl = rtrim(env('WEATHERAPI_BASE_URL', 'https://api.weatherapi.com/v1'), '/');
        $key = env('WEATHERAPI_KEY');
        if (!$key || !$location || !$eta) return null;

        $res = Http::timeout(10)->get($baseUrl . '/forecast.json', [
            'key' => $key,
            'q' => $location,
            'days' => 10,
            'aqi' => 'no',
            'alerts' => 'no',
        ]);
        if (!$res->ok()) return null;

        $data = $res->json();
        $loc = $data['location'] ?? [];
        $lat = $loc['lat'] ?? null;
        $lon = $loc['lon'] ?? null;

        $target = $eta->toDateString();
        $dayData = null;
        foreach (($data['forecast']['forecastday'] ?? []) as $day) {
            if (($day['date'] ?? null) === $target) {
                $d = $day['day'] ?? [];
                $c = $d['condition'] ?? [];
                $dayData = [
                    'date' => $day['date'] ?? null,
                    'condition_text' => $c['text'] ?? null,
                    'condition_icon' => $c['icon'] ?? null,
                    'avgtemp_c' => $d['avgtemp_c'] ?? null,
                    'maxwind_kph' => $d['maxwind_kph'] ?? null,
                    'gust_kph' => $d['maxwind_kph'] ?? null,
                    'daily_chance_of_rain' => $d['daily_chance_of_rain'] ?? null,
                ];
                break;
            }
        }

        $marine = [
            'swell_height_m' => null,
            'swell_period_s' => null,
            'swell_direction' => null,
            'wave_height_m' => null,
            'wave_direction' => null,
        ];

        if ($lat !== null && $lon !== null) {
            $m = Http::timeout(10)->get('https://marine-api.open-meteo.com/v1/marine', [
                'latitude' => $lat,
                'longitude' => $lon,
                'hourly' => 'wave_height,wave_direction,swell_height,swell_direction,swell_period',
                'start_date' => $eta->toDateString(),
                'end_date' => $eta->toDateString(),
                'timezone' => 'auto',
            ]);

            if ($m->ok()) {
                $mj = $m->json();
                $hourly = $mj['hourly'] ?? [];
                $times = $hourly['time'] ?? [];
                $idx = 0;
                if (is_array($times) && count($times) > 0) {
                    $etaHour = Carbon::parse($eta->format('Y-m-d H:00'));
                    $min = PHP_INT_MAX;
                    foreach ($times as $i => $t) {
                        $tt = Carbon::parse($t);
                        $diff = abs($tt->diffInMinutes($etaHour));
                        if ($diff < $min) { $min = $diff; $idx = $i; }
                    }
                }
                $marine['wave_height_m'] = $hourly['wave_height'][$idx] ?? null;
                $marine['wave_direction'] = $hourly['wave_direction'][$idx] ?? null;
                $marine['swell_height_m'] = $hourly['swell_height'][$idx] ?? null;
                $marine['swell_direction'] = $hourly['swell_direction'][$idx] ?? null;
                $marine['swell_period_s'] = $hourly['swell_period'][$idx] ?? null;
            }
        }

        if (!$dayData) return null;

        return array_merge($dayData, $marine);
    }
}