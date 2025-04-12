<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\Escandallo;

class SalesReportService
{
    public function getMargins(array $saleIds): array
    {
        $linesGrouped = SaleLine::with('escandallo')
            ->whereIn('sale_id', $saleIds)
            ->get()
            ->groupBy('escandallo_id');

        $margins = [];

        foreach ($linesGrouped as $escandalloId => $groupedLines) {
            $escandallo = Escandallo::find($escandalloId);

            $totalSale = $groupedLines->sum(fn($line) => $line->price * $line->quantity);
            $totalCost = $groupedLines->sum(fn($line) => $escandallo->food_cost * $line->quantity);
            $margin = $totalSale > 0 ? (($totalSale - $totalCost) / $totalSale) * 100 : 0;

            $margins[] = [
                'name' => $escandallo->name,
                'margin' => round($margin, 2),
            ];
        }

        return $margins;
    }

    public function getDailyVolumeStats(array $saleIds): array
    {
        $sales = Sale::with('lines')
            ->whereIn('id', $saleIds)
            ->get();

        $dailySales = [];

        foreach ($sales as $sale) {
            $date = $sale->date;

            $totalForSale = 0;
            foreach ($sale->lines as $line) {
                $totalForSale += $line->price * $line->quantity;
            }

            $dailySales[$date] = ($dailySales[$date] ?? 0) + $totalForSale;
        }

        $highestDay = array_keys($dailySales, max($dailySales))[0];
        $lowestDay = array_keys($dailySales, min($dailySales))[0];

        return [
            'max' => ['date' => $highestDay, 'value' => $dailySales[$highestDay]],
            'min' => ['date' => $lowestDay, 'value' => $dailySales[$lowestDay]],
        ];
    }
}
