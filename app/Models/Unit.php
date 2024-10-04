<?php

namespace App\Models;

use App\Enums\MeasurementType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'conversion_factor_to_base',
        'base_unit_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'measurement_type' => MeasurementType::class,
    ];

    /**
     * The base unit of the unit
     *
     * @return BelongsTo<Unit, Unit>
     */
    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    /**
     * The child units of the unit
     *
     * @return HasMany<Unit>
     */
    public function childUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'base_unit_id');
    }

    /**
     * The ingredients that use the unit
     *
     * @return HasMany<Ingredient>
     */
    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }

    /**
     * Check if the unit is a base unit
     *
     * @return Attribute<bool, void>
     */
    public function isBaseUnit(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->base_unit_id === null,
        );
    }
}
