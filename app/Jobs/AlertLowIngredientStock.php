<?php

namespace App\Jobs;

use App\Mail\AlertLowIngredientStockMail;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Mail;

class AlertLowIngredientStock implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Ingredient $ingredient)
    {
        //
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping((string)$this->ingredient->id)];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->ingredient->shouldBeAlertedForLowStock()) {
            $admin = User::find(1);
            $this->ingredient->alerted_at = now();
            $this->ingredient->save();
            Mail::to($admin)->send(new AlertLowIngredientStockMail($this->ingredient));
        }

    }
}
