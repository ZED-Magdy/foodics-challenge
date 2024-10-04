<?php

namespace App\Actions\Ingredients;

use App\Jobs\AlertLowIngredientStock;
use App\Models\Ingredient;

final readonly class DecreaseQuantityAction
{
    /**
     * Decrease the stock of the ingredient by the quantity of the order item.
     *
     * @param Ingredient $ingredient
     * @param int $orderItemQuantity
     *
     * @return Ingredient
     */
    public function handle(Ingredient $ingredient, int $orderItemQuantity): Ingredient
    {
        $ingredient->decrement('stock', $orderItemQuantity * $ingredient->pivot?->quantity);

        $shouldAlert = $ingredient->shouldBeAlertedForLowStock();

        // dump("STOCK", $orderItemQuantity * $ingredient->pivot?->quantity);
        // dump("SHOULD ALERT", $shouldAlert);

        AlertLowIngredientStock::dispatchIf($shouldAlert, $ingredient);

        return $ingredient;
    }
}
