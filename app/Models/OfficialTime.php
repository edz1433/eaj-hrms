<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficialTime extends Model
{
    use HasFactory;

    public const DEFAULT_MORNING = '08:00:00-12:00:00';
    public const DEFAULT_AFTERNOON = '13:00:00-17:00:00';
    public const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri'];

    protected $fillable = [
        'empid', 
        'morn_mon', 
        'aft_mon',
        'morn_tue',
        'aft_tue',
        'morn_wed',
        'aft_wed',
        'morn_thu',
        'aft_thu',
        'morn_fri',
        'aft_fri',
    ];

    public static function defaultAttributes(?string $empid = null): array
    {
        $attributes = [
            'morn_mon' => self::DEFAULT_MORNING,
            'aft_mon' => self::DEFAULT_AFTERNOON,
            'morn_tue' => self::DEFAULT_MORNING,
            'aft_tue' => self::DEFAULT_AFTERNOON,
            'morn_wed' => self::DEFAULT_MORNING,
            'aft_wed' => self::DEFAULT_AFTERNOON,
            'morn_thu' => self::DEFAULT_MORNING,
            'aft_thu' => self::DEFAULT_AFTERNOON,
            'morn_fri' => self::DEFAULT_MORNING,
            'aft_fri' => self::DEFAULT_AFTERNOON,
        ];

        if ($empid !== null) {
            $attributes['empid'] = $empid;
        }

        return $attributes;
    }

    public static function rangeColumns(): array
    {
        return collect(self::DAYS)
            ->flatMap(fn ($day) => ["morn_{$day}", "aft_{$day}"])
            ->values()
            ->all();
    }

    public static function splitRange(?string $range, string $defaultRange): array
    {
        $parts = explode('-', (string) $range, 2);

        if (count($parts) !== 2 || !self::isTime($parts[0]) || !self::isTime($parts[1])) {
            $parts = explode('-', $defaultRange, 2);
        }

        return [
            self::normalizeTime($parts[0]),
            self::normalizeTime($parts[1]),
        ];
    }

    public static function buildRange(string $start, string $end): string
    {
        return self::normalizeTime($start) . '-' . self::normalizeTime($end);
    }

    public static function normalizeTime(string $time): string
    {
        $value = trim($time);

        foreach (['H:i:s', 'H:i'] as $format) {
            $date = \DateTime::createFromFormat('!' . $format, $value);
            $errors = \DateTime::getLastErrors();
            $hasErrors = is_array($errors)
                && ($errors['warning_count'] > 0 || $errors['error_count'] > 0);

            if ($date && !$hasErrors) {
                return $date->format('H:i:s');
            }
        }

        throw new \InvalidArgumentException('Invalid time value.');
    }

    private static function isTime(?string $time): bool
    {
        if ($time === null || trim($time) === '') {
            return false;
        }

        try {
            self::normalizeTime($time);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function forEmployee(string $empid): self
    {
        $officialTime = self::firstOrCreate(
            ['empid' => $empid],
            self::defaultAttributes($empid)
        );

        $defaults = self::defaultAttributes();
        $missing = collect($defaults)
            ->filter(function ($value, $key) use ($officialTime) {
                if (empty($officialTime->{$key})) {
                    return true;
                }

                [$start, $end] = self::splitRange($officialTime->{$key}, $value);

                return $officialTime->{$key} !== "{$start}-{$end}";
            })
            ->map(function ($value, $key) use ($officialTime) {
                [$start, $end] = self::splitRange($officialTime->{$key}, $value);

                return "{$start}-{$end}";
            })
            ->all();

        if (!empty($missing)) {
            $officialTime->fill($missing)->save();
            $officialTime->refresh();
        }

        return $officialTime;
    }
}
