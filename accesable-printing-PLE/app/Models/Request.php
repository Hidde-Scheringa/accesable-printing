<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Request extends Model
{
    use HasFactory;

    /**
     * De attributen die massaal toewijsbaar zijn (Mass Assignment).
     * Zorg ervoor dat 'stripe_checkout_id' en 'payment_status' hierin staan,
     * anders kan de PaymentController de database niet bijwerken.
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'material',
        'color',
        'scale',
        'total_price',
        'stl_files',
        'city',
        'street',
        'streetnumber',
        'zipcode',
        'status',
        'stripe_checkout_id', // Voor het koppelen van de Stripe Sessie
        'payment_status',      // Voor de status: unpaid, pending, paid
    ];

    /**
     * De attributen die moeten worden omgezet naar specifieke types.
     */
    protected $casts = [
        'stl_files' => 'array',         // Zorgt dat de JSON in de DB een PHP array wordt
        'scale' => 'integer',
        'streetnumber' => 'integer',
        'total_price' => 'decimal:2',   // Zorgt voor 2 decimalen (bijv. 19.50)
    ];

    /**
     * Relatie: Een aanvraag behoort tot één gebruiker.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper methode: Is de order al betaald?
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Helper methode: Moet er nog betaald worden?
     */
    public function isPendingPayment(): bool
    {
        return $this->payment_status === 'pending';
    }
}
