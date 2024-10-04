<?php

namespace Tests\Actions\Ingredients;

use App\Actions\Ingredients\DecreaseQuantityAction;
use App\Jobs\AlertLowIngredientStock;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Unit;

test('handle will decrease the stock of a given ingredient', function () {
    \Queue::fake();
    $conversionFactorToBase = 1000;
    $unit = Unit::create([
        'title' => 'test',
        'conversion_factor_to_base' => $conversionFactorToBase,
        'base_unit_id' => null
    ]);

    $ingredient = new Ingredient();
    $ingredient->setRelation('unit', $unit);
    $ingredient->title = 'test';
    $ingredient->stock = 10;
    $ingredient->initial_stock = 10* $conversionFactorToBase;
    $ingredient->alerted_at = null;
    $ingredient->save();
    $product = new Product(['title' => 'test', 'description' => 'test']);
    $product->setRelation('ingredients', collect([
        $ingredient->setRelation('pivot', (object)['quantity' => 1, 'unit_id' => $unit->id])
    ]));
    $product->save();


    (new DecreaseQuantityAction())->handle($product->ingredients->first(), 5);

    expect($ingredient->stock)->toBe(5);
});

test('handle will decrease the stock of a given ingredient and push alert when stock is lte 50%', function () {
    \Queue::fake();
    $conversionFactorToBase = 1000;
    $unit = Unit::create([
        'title' => 'test',
        'conversion_factor_to_base' => $conversionFactorToBase,
        'base_unit_id' => null
    ]);

    $ingredient = new Ingredient();
    $ingredient->setRelation('unit', $unit);
    $ingredient->title = 'test';
    $ingredient->stock = 10;
    $ingredient->initial_stock = 10* $conversionFactorToBase;
    $ingredient->alerted_at = null;
    $ingredient->save();
    $product = new Product(['title' => 'test', 'description' => 'test']);
    $product->setRelation('ingredients', collect([
        $ingredient->setRelation('pivot', (object)['quantity' => 1, 'unit_id' => $unit->id])
    ]));
    $product->save();


    (new DecreaseQuantityAction())->handle($product->ingredients->first(), 5);

    expect($ingredient->stock)->toBe(5);
    \Queue::assertPushed(AlertLowIngredientStock::class, function ($job) use ($ingredient) {
        return $job->ingredient->is($ingredient);
    });
});
