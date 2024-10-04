<?php

use App\Enums\MeasurementType;
use App\Mail\AlertLowIngredientStockMail;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;

it('does not place the order if any product is invalid', function () {

    $payload = ['products' => [['product_id' => rand(1, 1000), 'quantity' => rand(1, 2)]]];

    $response = $this->postJson('/api/orders', $payload);

    expect($response->getStatusCode())->toBe(422);
});

it('places the order if all ingredients in stock and updates stock correctly', function ($beefStockInKG, $beefPerBurgerInGrams, $orderItemQuantity) {
    User::factory()->create();
    $baseUnit = Unit::create([
        'title' => 'Gram',
        'conversion_factor_to_base' => 1,
    ]);

    $childUnit = Unit::create([
        'title' => 'Kilogram',
        'conversion_factor_to_base' => 1000,
        'base_unit_id' => $baseUnit->id,
    ]);

    $ingredient = Ingredient::create([
        'title' => 'Beef',
        'unit_id' => $childUnit->id,
        'stock' => $beefStockInKG,
    ]);

    $product = Product::create([
        'title' => 'Beef Burger',
        'description' => 'A delicious beef burger',
    ]);

    $product->ingredients()->attach($ingredient->id, ['quantity' => $beefPerBurgerInGrams, 'unit_id' => $baseUnit->id]);

    $payload = ['products' => [['product_id' => $product->id, 'quantity' => $orderItemQuantity]]];

    $response = $this->postJson('/api/orders', $payload);
    $expectedRemainingBeefStock = $beefStockInKG - ($beefPerBurgerInGrams / 1000 * $orderItemQuantity);

    expect($response->getStatusCode())->toBe(201)
        ->and(Ingredient::find($ingredient->id)->stock)->toBe($expectedRemainingBeefStock);

})->with([[20, 1000, 3], [10, 1000, 10], [15, 1000, 14],]);


it('does not place the order if any ingredient is out of stock', function ($beefStockInKG, $beefPerBurgerInGrams, $orderItemQuantity) {
    $baseUnit = Unit::create([
        'title' => 'Gram',
        'conversion_factor_to_base' => 1,
    ]);

    $childUnit = Unit::create([
        'title' => 'Kilogram',
        'conversion_factor_to_base' => 1000,
        'base_unit_id' => $baseUnit->id,
    ]);

    $ingredient = Ingredient::create([
        'title' => 'Beef',
        'unit_id' => $childUnit->id,
        'stock' => $beefStockInKG,
    ]);

    $product = Product::create([
        'title' => 'Beef Burger',
        'description' => 'A delicious beef burger',
    ]);

    $product->ingredients()->attach($ingredient->id, ['quantity' => $beefPerBurgerInGrams, 'unit_id' => $baseUnit->id]);

    $payload = ['products' => [['product_id' => $product->id, 'quantity' => $orderItemQuantity]]];

    $response = $this->postJson('/api/orders', $payload);

    expect($response->getStatusCode())->toBe(400)
        ->and(Ingredient::find($ingredient->id)->stock)->toBe($beefStockInKG);

})->with([[20, 1000, 21], [10, 1000, 11], [0, 1000, 1],]);

it('should send mail to admin when ingredient stock is less than or equal 50%', function () {
    Mail::fake();

    $baseUnit = Unit::create([
        'title' => 'Gram',
        'conversion_factor_to_base' => 1,
    ]);

    $childUnit = Unit::create([
        'title' => 'Kilogram',
        'conversion_factor_to_base' => 1000,
        'base_unit_id' => $baseUnit->id,
    ]);

    $ingredient = Ingredient::create([
        'title' => 'Beef',
        'unit_id' => $childUnit->id,
        'stock' => 10,
    ]);

    $product = Product::create([
        'title' => 'Beef Burger',
        'description' => 'A delicious beef burger',
    ]);

    $product->ingredients()->attach($ingredient->id, ['quantity' => 1000, 'unit_id' => $baseUnit->id]);

    $payload = ['products' => [['product_id' => $product->id, 'quantity' => 5]]];

    $this->postJson('/api/orders', $payload);

    Mail::assertQueued(function (AlertLowIngredientStockMail $mail) use ($ingredient) {
        return $mail->ingredient->id === $ingredient->id;
    });
});

it('should not send mail to admin when ingredient stock is more than 50%', function () {
    Mail::fake();
    User::factory()->create();
    $baseUnit = Unit::create([
        'title' => 'Gram',
        'conversion_factor_to_base' => 1,
    ]);

    $childUnit = Unit::create([
        'title' => 'Kilogram',
        'conversion_factor_to_base' => 1000,
        'base_unit_id' => $baseUnit->id,
    ]);

    $ingredient = Ingredient::create([
        'title' => 'Beef',
        'unit_id' => $childUnit->id,
        'stock' => 10,
    ]);

    $product = Product::create([
        'title' => 'Beef Burger',
        'description' => 'A delicious beef burger',
    ]);

    $product->ingredients()->attach($ingredient->id, ['quantity' => 1000, 'unit_id' => $baseUnit->id]);

    $payload = ['products' => [['product_id' => $product->id, 'quantity' => 4]]];

    $this->postJson('/api/orders', $payload);

    Mail::assertNotQueued(function (AlertLowIngredientStockMail $mail) use ($ingredient) {
        return $mail->ingredient->id === $ingredient->id;
    });
});

it('should not send mail to admin when ingredient has been alerted before', function () {
    Mail::fake();

    $baseUnit = Unit::create([
        'title' => 'Gram',
        'conversion_factor_to_base' => 1,
    ]);

    $childUnit = Unit::create([
        'title' => 'Kilogram',
        'conversion_factor_to_base' => 1000,
        'base_unit_id' => $baseUnit->id,
    ]);

    $ingredient = Ingredient::create([
        'title' => 'Beef',
        'unit_id' => $childUnit->id,
        'stock' => 10,
        'alerted_at' => now(),
    ]);

    $product = Product::create([
        'title' => 'Beef Burger',
        'description' => 'A delicious beef burger',
    ]);

    $product->ingredients()->attach($ingredient->id, ['quantity' => 1000, 'unit_id' => $baseUnit->id]);

    $payload = ['products' => [['product_id' => $product->id, 'quantity' => 5]]];

    $this->postJson('/api/orders', $payload);

    Mail::assertNotQueued(AlertLowIngredientStockMail::class);
});

it('should only send the mail once when the stock is below 50%', function (){
    Mail::fake();

    $baseUnit = Unit::create([
        'title' => 'Gram',
        'conversion_factor_to_base' => 1,
    ]);

    $childUnit = Unit::create([
        'title' => 'Kilogram',
        'conversion_factor_to_base' => 1000,
        'base_unit_id' => $baseUnit->id,
    ]);

    $ingredient = Ingredient::create([
        'title' => 'Beef',
        'unit_id' => $childUnit->id,
        'stock' => 10,
    ]);

    $product = Product::create([
        'title' => 'Beef Burger',
        'description' => 'A delicious beef burger',
    ]);

    $product->ingredients()->attach($ingredient->id, ['quantity' => 1000, 'unit_id' => $baseUnit->id]);

    $payload = ['products' => [['product_id' => $product->id, 'quantity' => 5]]];

    $this->postJson('/api/orders', $payload);
    $this->postJson('/api/orders', $payload);

    Mail::assertQueued(AlertLowIngredientStockMail::class, 1);
});
