<?php

namespace Tests\Unit;

use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\Escandallo;
use App\Services\SalesReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SalesReportServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_calculates_profit_margins_correctly()
    {
        $nachos = Escandallo::create(['name' => 'Nachos', 'food_cost' => 5.00]);
        $ron = Escandallo::create(['name' => 'Ron Cola', 'food_cost' => 2.00]);

        $sale = Sale::create(['date' => '2024-04-01', 'sale_number' => 1]);

        SaleLine::create([
            'sale_id' => $sale->id,
            'escandallo_id' => $nachos->id,
            'quantity' => 2,
            'price' => 10.00
        ]);

        SaleLine::create([
            'sale_id' => $sale->id,
            'escandallo_id' => $ron->id,
            'quantity' => 1,
            'price' => 8.00
        ]);

        $report = new SalesReportService();

        $margins = collect($report->getMargins([$sale->id]))->keyBy('name');

        $this->assertEqualsWithDelta(50.00, $margins['Nachos']['margin'], 0.01);
        $this->assertEqualsWithDelta(75.00, $margins['Ron Cola']['margin'], 0.01);
    }

    #[Test]
    public function it_detects_highest_and_lowest_volume_days()
    {
        $esc = Escandallo::create(['name' => 'Item', 'food_cost' => 1.00]);

        $sale1 = Sale::create(['date' => '2024-04-01', 'sale_number' => 1]);
        $sale2 = Sale::create(['date' => '2024-04-02', 'sale_number' => 2]);

        SaleLine::create([
            'sale_id' => $sale1->id,
            'escandallo_id' => $esc->id,
            'quantity' => 2,
            'price' => 10.00
        ]);

        SaleLine::create([
            'sale_id' => $sale2->id,
            'escandallo_id' => $esc->id,
            'quantity' => 1,
            'price' => 5.00
        ]);

        $report = new SalesReportService();
        $stats = $report->getDailyVolumeStats([$sale1->id, $sale2->id]);

        $this->assertEquals('2024-04-01', $stats['max']['date']);
        $this->assertEquals(20.00, $stats['max']['value']);

        $this->assertEquals('2024-04-02', $stats['min']['date']);
        $this->assertEquals(5.00, $stats['min']['value']);
    }
}
