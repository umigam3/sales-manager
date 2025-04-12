<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Escandallo;

class EscandalloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Escandallo::insert([
            ['id' => 2, 'name' => 'Nachos con guacamole', 'food_cost' => 5.51],
            ['id' => 3, 'name' => 'Ron cola', 'food_cost' => 2.25],
        ]);
    }
}
