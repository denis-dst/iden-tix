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

    public function kioskPhotos()
    {
        return $this->hasMany(KioskPhoto::class);
    }
}
