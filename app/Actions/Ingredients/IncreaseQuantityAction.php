<?php

namespace App\Actions\Ingredients;

use App\Models\Ingredient;
use App\Models\OrderItem;

final readonly class IncreaseQuantityAction
{

    /**
     * Increase the stock of the ingredient by the quantity of the order item.
     *
     * @param Ingredient $ingredient
     * @param int $orderItemQuantity
     *
     * @return Ingredient
     */
    public function handle(Ingredient $ingredient, int $orderItemQuantity): Ingredient
    {
        $ingredient->increment('stock', $orderItemQuantity * $ingredient->pivot?->quantity);
        $ingredient->save();

        return $ingredient;
    }
}
