<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KioskPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'event_id',
        'transaction_id',
        'category_name',
        'customer_name',
        'customer_email',
        'customer_phone',
        'original_photo',
        'card_photo',
        'token',
        'download_count',
        'email_sent_at',
    ];

    protected $casts = [
        'email_sent_at' => 'datetime',
        'download_count' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($photo) {
            if (empty($photo->token)) {
                $photo->token = Str::random(32);
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
