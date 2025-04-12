<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleLine;

class SalesProcessorService
{
    public function process(array $lines): array
    {
        $salesByDate = collect($lines)->groupBy('date');
        $currentSalesIds = [];
        $saleNumber = Sale::max('sale_number') ?? 0;

        foreach ($salesByDate as $date => $linesForDate) {
            $sale = Sale::create([
                'date' => $date,
                'sale_number' => ++$saleNumber,
            ]);

            foreach ($linesForDate as $line) {
                SaleLine::create([
                    'sale_id' => $sale->id,
                    'escandallo_id' => $line['escandallo_id'],
                    'quantity' => $line['quantity'],
                    'price' => $line['price'],
                ]);
            }

            $currentSalesIds[] = $sale->id;
        }

        return $currentSalesIds;
    }
}
