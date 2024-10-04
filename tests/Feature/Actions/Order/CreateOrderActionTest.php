<?php

namespace Tests\Actions\Order;

use App\Actions\Order\CreateOrderAction;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Unit;
use Exception;
use Illuminate\Support\Facades\Queue;

test('handle will create order when given data is ok', function () {
     Queue::fake();

     $conversionFactorToBase = 1000;

     $unit = Unit::create([
         'title' => 'test',
         'conversion_factor_to_base' => $conversionFactorToBase,
         'base_unit_id' => null
     ]);

     $ingredient = new Ingredient();
     $ingredient->unit_id = $unit->id;
     $ingredient->title = 'test';
     $ingredient->stock = 10;
     $ingredient->initial_stock = 10* $conversionFactorToBase;
     $ingredient->alerted_at = null;
     $ingredient->save();

     $product = new Product(['title' => 'test', 'description' => 'test']);
     $product->save();

    $product->ingredients()->attach($ingredient->id, [
        'quantity' => 1,
        'unit_id' => $unit->id
    ]);

    $order = (new CreateOrderAction())->handle([
        [
            'product_id' => $product->id,
            'quantity' => 1
        ]
    ]);

    expect($order->items->count())->toBe(1)
        ->and($order->items->first()->product_id)->toBe($product->id)
        ->and($order->items->first()->quantity)->toBe(1);
});

test('handle will not create order when there is a product out of stock', function () {
    Queue::fake();

    $conversionFactorToBase = 1000;

    $unit = Unit::create([
        'title' => 'test',
        'conversion_factor_to_base' => $conversionFactorToBase,
        'base_unit_id' => null
    ]);

    $ingredient = new Ingredient();
    $ingredient->unit_id = $unit->id;
    $ingredient->title = 'test';
    $ingredient->stock = 10;
    $ingredient->initial_stock = 10* $conversionFactorToBase;
    $ingredient->alerted_at = null;
    $ingredient->save();

    $product = new Product(['title' => 'test', 'description' => 'test']);
    $product->save();
    $product->ingredients()->attach($ingredient->id, [
        'quantity' => 1000,
        'unit_id' => $unit->id
    ]);

    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Ingredient {$ingredient->title} is out of stock");
    (new CreateOrderAction())->handle([
        [
            'product_id' => $product->id,
            'quantity' => 15
        ]
    ]);
});
