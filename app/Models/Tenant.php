<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'slug', 'email', 'phone', 'address', 'logo', 
        'status', 'payment_server_key', 'payment_client_key', 
        'meta', 'synced_at', 'terms_conditions'
    ];

    protected $casts = [
        'meta' => 'array',
        'synced_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function gates()
    {
        return $this->hasMany(Gate::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function membershipTiers()
    {
        return $this->hasMany(MembershipTier::class);
    }

    public function members()
    {
        return $this->hasMany(TenantMember::class);
    }

    public function seasonPasses()
    {
        return $this->hasMany(SeasonPass::class);
    }

    public function korwils()
    {
        return $this->hasMany(Korwil::class);
    }

    public function fanQuizzes()
    {
        return $this->hasMany(FanQuiz::class);
    }

    public function wristbandTemplates()
    {
        return $this->hasMany(WristbandTemplate::class);
    }

    public function photoboothTemplates()
    {
        return $this->hasMany(PhotoboothTemplate::class);
    }

    public function masterPhotoboothTemplate()
    {
        return $this->hasOne(PhotoboothTemplate::class)->whereNull('event_id');
    }

    public function getEffectivePhotoboothTemplate(): PhotoboothTemplate
    {
        return $this->masterPhotoboothTemplate()->first() ?: PhotoboothTemplate::create([
            'tenant_id' => $this->id,
            'event_id' => null,
            'name' => 'Master Template ' . $this->name,
            'is_active' => true,
            'primary_color' => '#5A2D82',
            'secondary_color' => '#F95700',
            'header_title' => 'I LEAGUE 2026/2027',
            'header_subtitle' => 'FOOTBALL UNITES US',
            'badge_text' => 'MATCHDAY COLLECTIBLE',
            'category_label_format' => '{category} • MATCHDAY COLLECTIBLE',
            'footer_text' => 'SATU LIGA, SATU SEMANGAT',
            'show_qr' => true,
            'show_fan_name' => true,
            'show_category' => true,
            'show_match_info' => true,
            'show_team_logos' => true,
        ]);
    }

    public function kioskPhotos()
    {
        return $this->hasMany(KioskPhoto::class);
    }
}
