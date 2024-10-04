<?php

namespace App\Observers;

use App\Models\Ingredient;

class IngredientObserver
{
    public function created(Ingredient $ingredient): void
    {
        $ingredient->initial_stock = $ingredient->stock * $ingredient->unit?->conversion_factor_to_base;
        $ingredient->save();
    }
}
