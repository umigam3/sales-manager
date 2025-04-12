<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\Escandallo;
use Carbon\Carbon;
use App\Services\SalesProcessorService;
use App\Services\SalesReportService;

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
        $this->info('Deja vacio y presiona enter para finalizar.');
    
        $lines = [];
    
        while (true) {
            $input = $this->ask('Ejemplo: 2024-07-02,2,10,10');
            if (empty($input)) break;
    
            $parts = explode(',', $input);
    
            $validated = $this->validateLine($parts);

            if (!$validated) continue;
    
            $lines[] = $validated;
        }
    
        $processor = new SalesProcessorService();
        $report = new SalesReportService();
    
        $saleIds = $processor->process($lines);
    
        $this->line("Margen de beneficio de cada escandallo:");
        foreach ($report->getMargins($saleIds) as $item) {
            $this->line(" - {$item['name']}: {$item['margin']}%");
        }
    
        $stats = $report->getDailyVolumeStats($saleIds);
    
        $this->line("Día con mayor volumen de ventas: {$stats['max']['date']}, {$stats['max']['value']}");
        $this->line("Día con menor volumen de ventas: {$stats['min']['date']}, {$stats['min']['value']}");
    }

    private function validateLine(array $parts): ?array
    {
        if (count($parts) !== 4) {
            $this->error('Formato invalido.');
            return null;
        }

        [$date, $escandallo_id, $quantity, $price] = $parts;

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->error("Formato de fecha inválido: '$date'. Usa el formato YYYY-MM-DD.");
            return null;
        }

        if (!is_numeric($escandallo_id) || !is_numeric($quantity) || !is_numeric($price)) {
            $this->error("escandallo_id, cantidad y precio deben ser valores numéricos.");
            return null;
        }

        return [
            'date' => $date,
            'escandallo_id' => (int)$escandallo_id,
            'quantity' => (int)$quantity,
            'price' => (float)$price,
        ];
    }
}    