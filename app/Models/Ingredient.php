<?php

namespace App\Models;

use App\Observers\IngredientObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property object{quantity: int, unit_id: int, product_id: int, ingredient_id: int}|null $pivot
 */
#[ObservedBy(IngredientObserver::class)]
class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'stock',
        'unit_id',
        'initial_stock',
        'alerted_at',
    ];

    /**
     * The unit of the ingredient
     *
     * @return BelongsTo<Unit, Ingredient>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * The products that use the ingredient
     *
     * @return BelongsToMany<Product>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_ingredient');
    }

    /**
     * Get the stock attribute in base unit
     * @return Attribute<int, int>
     */
    public function stock(): Attribute
    {
        //convert all stock to base unit to avoid floats
        return Attribute::make(
            get: fn($value) => $value / $this->unit?->conversion_factor_to_base,
            set: fn($value) => $value * $this->unit?->conversion_factor_to_base,
        );
    }

    /**
     * Check if the ingredient should be alerted for low stock.
     * If the stock is less than or equal to half of the initial stock, and it has not been alerted before
     * @return bool
     */
    public function shouldBeAlertedForLowStock(): bool
    {
        $currentStock = $this->stock * $this->unit?->conversion_factor_to_base;
        $halfOfInitialStock = $this->initial_stock * 0.5;
        $wasNotAlerted = $this->alerted_at === null;

        return $currentStock <= $halfOfInitialStock && $wasNotAlerted;
    }
}
