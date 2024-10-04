<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $gram_unit_id = 1;
        $beef_id = 1;
        $cheese_id = 2;
        $onion_id = 3;
        Product::create([
            'title' => 'Cheeseburger',
            'description' => 'A cheeseburger is a hamburger topped with cheese. Traditionally, the slice of cheese is placed on top of the meat patty, but the burger can include many variations in structure, ingredients, and composition.',
        ])->ingredients()->attach([
            $beef_id => ['quantity' => 150, 'unit_id' => $gram_unit_id],
            $cheese_id => ['quantity' => 30, 'unit_id' => $gram_unit_id],
            $onion_id => ['quantity' => 20, 'unit_id' => $gram_unit_id],
        ]);
    }
}
