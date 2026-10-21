<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id', 'tenant_id', 'name', 'description', 'price', 'nik_restriction', 'nik_restriction_message', 'badge_text',
        'quota', 'sold_count', 'hex_color', 'category_image', 
        'background_image', 'layout_config', 'is_active', 
        'sale_start_at', 'sale_end_at', 'sort_order'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'layout_config' => 'array',
        'sale_start_at' => 'datetime',
        'sale_end_at' => 'datetime',
    ];

    public function setSoldCountAttribute($value): void
    {
        $this->attributes['sold_count'] = max(0, (int) $value);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function gates()
    {
        return $this->belongsToMany(Gate::class, 'gate_ticket_category');
    }

    public function wristbandTemplate()
    {
        return $this->hasOne(WristbandTemplate::class)->latestOfMany();
    }

    public function getEffectiveWristbandTemplate(): ?WristbandTemplate
    {
        return $this->wristbandTemplate ?: $this->event?->getEffectiveWristbandTemplate();
    }

    public function isAvailable()
    {
        if (!$this->is_active) return false;
        if ($this->sold_count >= $this->quota) return false;
        
        $now = now();
        if ($this->sale_start_at && $now->lt($this->sale_start_at)) return false;
        if ($this->sale_end_at && $now->gt($this->sale_end_at)) return false;

        return true;
    }
}
