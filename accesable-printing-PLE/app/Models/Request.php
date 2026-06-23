<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Request extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
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
        'defect_reason',
        'defect_image_path',
        'suggested_refund',
        'cancellation_details',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'stl_files' => 'array',
        'scale' => 'integer',
        'streetnumber' => 'integer',
        'total_price' => 'decimal:2',
        'defect_image_path' => 'array',
    ];

    /**
     * Get the user that owns the request.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the order payment is completed.
     *
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if the order is awaiting payment.
     *
     * @return bool
     */
    public function isPendingPayment(): bool
    {
        return $this->payment_status === 'pending';
    }
}
