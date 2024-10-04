<?php

namespace App\Mail;

use App\Models\Ingredient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlertLowIngredientStockMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Ingredient $ingredient)
    {
    }

    /**
     * Build the envelope for the message.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Low Ingredient Stock Alert',
        );
    }

    /**
     * Build the message.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.alert-low-ingredient-stock-email',
            with: ['ingredient' => $this->ingredient],
        );
    }
}
