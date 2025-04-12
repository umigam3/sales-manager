<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\Escandallo;
use Carbon\Carbon;

class ProcessSales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-sales';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes sales input and calculates margins and daily volume stats';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Introduce lineas de venta con este formato: fecha, escandallo_id, cantidad, precio');
        $this->info('Deja vacio y presiona enter para finalizar');

        $lines = [];

        while (true) {
            $input = $this->ask('Ejemplo: 2024-07-02,2,10,10');
            if (empty($input)) break;

            $dissectedInput = explode(',', $input);

            if (count($dissectedInput) !== 4) {
                $this->error('Formato Invalido.');
                continue;
            }

            [$date, $escandallo_id, $quantity, $price] = $dissectedInput;


            $lines[] = [
                'date' => $date,
                'escandallo_id' => (int)$escandallo_id,
                'quantity' => (int)$quantity,
                'price' => (int)$price,
            ];
        }

        $grouped = collect($lines)->groupBy('date');
        $currentSalesIds = [];

        foreach ($grouped as $date => $groupedLines) {
            $sale = Sale::create([
                'date' => $date,
                'sale_number' => Sale::max('sale_number') + 1,
            ]);
               
            $currentSalesIds[] = $sale->id;
            
            foreach ($groupedLines as $line) {
                $saleLine = SaleLine::create([
                    'sale_id' => $sale->id,
                    'escandallo_id' => $line['escandallo_id'],
                    'quantity' => $line['quantity'],
                    'price' => $line['price'],
                ]);
            }
        }

        $linesGrouped = SaleLine::with('escandallo')
            ->whereIn('sale_id', $currentSalesIds)
            ->get()
            ->groupBy('escandallo_id');

        $this->line("Margen de beneficio de cada escandallo:");
        foreach ($linesGrouped as $escandalloId => $groupedLines) {
            $escandallo = Escandallo::find($escandalloId);
            
            $totalSale = $groupedLines->sum(function ($line) {
                return $line->price * $line->quantity;
            });
            $totalCost = $groupedLines->sum(function ($line) use ($escandallo) {
                return $escandallo->food_cost * $line->quantity;
            });
            
            $margin = $totalSale > 0 ? (($totalSale - $totalCost) / $totalSale) * 100 : 0;

            $this->line(" - {$escandallo->name}: " . number_format($margin, 2) . '%');
        }

        $dailySales = [];
        $sales = Sale::with('lines')
            ->whereIn('id', $currentSalesIds)
            ->get();
        
        foreach ($sales as $sale) {
            $date = $sale->date;
        
            $totalForSale = 0;
            foreach ($sale->lines as $line) {
                $totalForSale += $line->price * $line->quantity;
            }
        
            if (!isset($dailySales[$date])) {
                $dailySales[$date] = 0;
            }
        
            $dailySales[$date] += $totalForSale;
        }

        $highestDay = array_keys($dailySales, max($dailySales))[0];
        $lowestDay = array_keys($dailySales, min($dailySales))[0];
        
        $highestValue = $dailySales[$highestDay];
        $lowestValue = $dailySales[$lowestDay];
        
        $this->line("Day with highest sales volume: $highestDay, $highestValue");
        $this->line("Day with lowest sales volume: $lowestDay, $lowestValue");
    }
}
