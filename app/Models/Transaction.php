<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'event_id', 'ticket_category_id', 'quantity', 'promo_code_id',
        'user_id', 'reference_no', 
        'customer_name', 'customer_email', 'customer_phone', 'customer_nik',
        'customer_gender', 'customer_umroh_answer',
        'discount_amount', 'total_amount', 'payment_status', 'payment_method', 
        'payment_reference', 'channel', 'processed_by', 'paid_at'
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::updated(function ($transaction) {
            $syncData = [];
            if ($transaction->wasChanged('event_id')) {
                $syncData['event_id'] = $transaction->event_id;
            }
            if ($transaction->wasChanged('ticket_category_id')) {
                $syncData['ticket_category_id'] = $transaction->ticket_category_id;
            }
            if ($transaction->wasChanged('tenant_id')) {
                $syncData['tenant_id'] = $transaction->tenant_id;
            }
            if (!empty($syncData)) {
                $transaction->tickets()->update($syncData);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class, 'promo_code_id');
    }

    /**
     * Scope query to only include legitimate sales transactions (online or direct POS sales),
     * excluding gate wristband scans and offline stock placeholders.
     */
    public function scopeSalesOnly($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('payment_method')
              ->orWhereNotIn('payment_method', ['OFFLINE_WRISTBAND', 'OFFLINE STOCK']);
        })
        ->where(function ($q) {
            $q->whereNull('reference_no')
              ->orWhere(function ($sub) {
                  $sub->where('reference_no', 'not like', 'WB-%')
                      ->where('reference_no', 'not like', 'STOCK-%');
              });
        })
        ->where(function ($q) {
            $q->whereNull('customer_email')
              ->orWhere('customer_email', '!=', 'wristband@gentix-apps.com');
        })
        ->where(function ($q) {
            $q->whereNull('customer_name')
              ->orWhere(function ($sub) {
                  $sub->where('customer_name', 'not like', 'Gelang Fisik %')
                      ->where('customer_name', '!=', 'OFFLINE STOCK');
              });
        });
    }

    /**
     * Check if this transaction originates from a gate wristband scan or offline stock placeholder.
     */
    public function isGateWristband(): bool
    {
        if (in_array($this->payment_method, ['OFFLINE_WRISTBAND', 'OFFLINE STOCK'], true)) {
            return true;
        }

        if ($this->customer_email === 'wristband@gentix-apps.com') {
            return true;
        }

        if ($this->reference_no && (str_starts_with($this->reference_no, 'WB-') || str_starts_with($this->reference_no, 'STOCK-'))) {
            return true;
        }

        if ($this->customer_name && (str_starts_with($this->customer_name, 'Gelang Fisik ') || $this->customer_name === 'OFFLINE STOCK')) {
            return true;
        }

        return false;
    }

    /**
     * Check if this transaction is an online or direct POS sale.
     */
    public function isSale(): bool
    {
        return !$this->isGateWristband();
    }
}
