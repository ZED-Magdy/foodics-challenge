<?php

namespace Database\Factories;

use App\Enums\MeasurementType;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'conversion_factor_to_base' => $this->faker->randomFloat(),
            'measurement_type' => $this->faker->randomElement([MeasurementType::WEIGHT, MeasurementType::VOLUME]),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'base_unit_id' => Unit::factory(),
        ];
    }
}
