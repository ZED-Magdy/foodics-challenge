<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientsSeeder extends Seeder
{
    public function run(): void
    {
        $kilograms_unit_id = 2;

        $ingredients = [
            [
                'title' => 'Beef',
                'unit_id' => $kilograms_unit_id,
                'stock' => 20,
            ],
            [
                'title' => 'Cheese',
                'unit_id' => $kilograms_unit_id,
                'stock' => 5,
            ],
            [
                'title' => 'Onion',
                'unit_id' => $kilograms_unit_id,
                'stock' => 1,
            ]
        ];

        foreach ($ingredients as $ingredient) {
            Ingredient::create($ingredient);
        }
    }
}
