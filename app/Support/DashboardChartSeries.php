<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DashboardChartSeries
{
    public static function monthExpression(string $column = 'created_at'): string
    {
        if (!in_array($column, ['created_at', 'paid_at', 'orders.paid_at'], true)) {
            throw new InvalidArgumentException('Unsupported chart date column.');
        }

        return match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', {$column})",
            'pgsql' => "to_char({$column}, 'YYYY-MM')",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    public static function sixMonths(Collection $rows): array
    {
        $rowsByMonth = $rows->keyBy('month');

        return collect(range(5, 0))->map(function (int $monthsAgo) use ($rowsByMonth): array {
            $month = now()->startOfMonth()->subMonths($monthsAgo);
            $row = $rowsByMonth->get($month->format('Y-m'));

            return [
                'label' => $month->locale('id')->translatedFormat('M'),
                'value' => max(0, (float) ($row->total ?? 0)),
            ];
        })->all();
    }
}