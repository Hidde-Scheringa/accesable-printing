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
        'stripe_checkout_id',
        'payment_status',

        // --- NIEUW: Deze stonden er nog niet in! ---
        'defect_reason',       // Zorgt dat de reden mag worden opgeslagen
        'defect_image_path',   // Zorgt dat het fotopad mag worden opgeslagen
        'suggested_refund'
    ];

    /**
     * De attributen die moeten worden omgezet naar specifieke types.
     */
    protected $casts = [
        'stl_files' => 'array',
        'scale' => 'integer',
        'streetnumber' => 'integer',
        'total_price' => 'decimal:2',
        'defect_image_path' => 'array',
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
