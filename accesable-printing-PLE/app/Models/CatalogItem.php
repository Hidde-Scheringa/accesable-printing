<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CatalogItem extends Model
{
    // Velden die via een formulier gevuld mogen worden
    protected $fillable = [
        'title',
        'slug',
        'description',
        'category',
        'price',
        'stl_files',
        'is_active',
        'is_featured'
    ];

    // Zorgt dat data types correct worden omgezet
    protected $casts = [
        'stl_files' => 'array',    // Belangrijk: zet JSON om naar een PHP array
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Optioneel: Genereer de slug automatisch op basis van de titel
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->slug)) {
                $item->slug = Str::slug($item->title);
            }
        });
    }
}
