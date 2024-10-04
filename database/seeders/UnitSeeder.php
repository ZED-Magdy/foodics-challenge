<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            [
                'title' => 'Gram',
                'conversion_factor_to_base' => 1,
                'base_unit_id' => null,
            ],
            [
                'title' => 'Kilogram',
                'conversion_factor_to_base' => 1000,
                'base_unit_id' => 1,
            ],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
