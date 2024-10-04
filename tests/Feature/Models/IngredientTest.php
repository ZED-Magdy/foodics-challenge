<?php

namespace Tests\Models;

use App\Models\Ingredient;
use App\Models\Unit;

test('shouldBeAlertedForLowStock will return false when the stock is bigger than 50%', function () {

    $unit = Unit::make([
        'title' => 'test',
        'conversion_factor_to_base' => 1,
        'base_unit_id' => null
    ]);

    $ingredient = new Ingredient();
    $ingredient->setRelation('unit', $unit);
    $ingredient->stock = 6;
    $ingredient->initial_stock = 10;
    $ingredient->alerted_at = null;

    $result = $ingredient->shouldBeAlertedForLowStock();

    expect($result)->toBeFalse();
});

test('shouldBeAlertedForLowStock will return true when the stock is less than or equals 50%', function () {

    $conversionFactorToBase = 1000;
    $unit = Unit::make([
        'title' => 'test',
        'conversion_factor_to_base' => $conversionFactorToBase,
        'base_unit_id' => null
    ]);

    $ingredient = new Ingredient();
    $ingredient->setRelation('unit', $unit);
    $ingredient->stock = 5;
    $ingredient->initial_stock = 10* $conversionFactorToBase;
    $ingredient->alerted_at = null;

    $result = $ingredient->shouldBeAlertedForLowStock();

    expect($result)->toBeTrue();
});

test('shouldBeAlertedForLowStock will return false when the stock is less than or equals 50% and already alerted', function () {

    $conversionFactorToBase = 1000;
    $unit = Unit::make([
        'title' => 'test',
        'conversion_factor_to_base' => $conversionFactorToBase,
        'base_unit_id' => null
    ]);

    $ingredient = new Ingredient();
    $ingredient->setRelation('unit', $unit);
    $ingredient->stock = 5;
    $ingredient->initial_stock = 10* $conversionFactorToBase;
    $ingredient->alerted_at = now();

    $result = $ingredient->shouldBeAlertedForLowStock();

    expect($result)->toBeFalse();
});




