<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'name', 'type', 'slug', 'description', 'terms_conditions', 'venue', 'city', 'google_maps_url',
        'background_image', 'banner_image', 'event_start_date', 
        'event_end_date', 'gate_open_at', 'gate_close_at', 'status', 'meta', 'security_code',
        'is_free', 'max_tickets_per_transaction', 'umroh_question_enabled', 'evoucher_info',
        'purchase_flow', 'thermal_paper_width_mm', 'thermal_paper_height_mm',
        'season_name', 'home_team_name', 'away_team_name', 'home_team_logo', 'away_team_logo',
        'home_score', 'away_score', 'allow_season_pass', 'season_pass_claim_days_before',
        'is_member_priority_sale', 'member_priority_start_at', 'general_sale_start_at',
    ];

    protected $casts = [
        'event_start_date' => 'datetime',
        'event_end_date' => 'datetime',
        'gate_open_at' => 'datetime',
        'gate_close_at' => 'datetime',
        'member_priority_start_at' => 'datetime',
        'general_sale_start_at' => 'datetime',
        'meta' => 'array',
        'is_free' => 'boolean',
        'allow_season_pass' => 'boolean',
        'is_member_priority_sale' => 'boolean',
        'season_pass_claim_days_before' => 'integer',
        'max_tickets_per_transaction' => 'integer',
        'umroh_question_enabled' => 'boolean',
        'thermal_paper_width_mm' => 'integer',
        'thermal_paper_height_mm' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function ticketCategories()
    {
        return $this->hasMany(TicketCategory::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function gates()
    {
        return $this->hasMany(Gate::class);
    }

    public function seasonPassClaims()
    {
        return $this->hasMany(SeasonPassClaim::class);
    }

    public function matchPredictions()
    {
        return $this->hasMany(MatchPrediction::class);
    }

    public function korwilAllocations()
    {
        return $this->hasMany(KorwilAllocation::class);
    }

    public function wristbandTemplate()
    {
        return $this->hasOne(WristbandTemplate::class)->whereNull('ticket_category_id')->latestOfMany();
    }

    public function getEffectiveWristbandTemplate(): ?WristbandTemplate
    {
        if ($this->relationLoaded('wristbandTemplate') && $this->wristbandTemplate) {
            return $this->wristbandTemplate;
        }

        return $this->wristbandTemplate()->first()
            ?: WristbandTemplate::where('event_id', $this->id)->latest()->first();
    }

    // Current occupancy calculation
    public function getCurrentOccupancyAttribute()
    {
        $in = GateLog::where('event_id', $this->id)->where('type', 'IN')->count();
        $out = GateLog::where('event_id', $this->id)->where('type', 'OUT')->count();
        return $in - $out;
    }

    /**
     * Get the dynamic upload tasks/proofs required for registration.
     */
    public function getRegistrationProofs(): array
    {
        if (isset($this->meta['registration_proofs']) && is_array($this->meta['registration_proofs'])) {
            return $this->meta['registration_proofs'];
        }

        // If custom fields are configured, do not inject legacy default Batik Umrah proofs
        if (!empty($this->meta['custom_fields'])) {
            return [];
        }

        $proofs = [];
        if ($this->meta['proof_ig_required'] ?? true) {
            $proofs[] = [
                'id' => 'proof_ig',
                'label' => 'Bukti follow IG',
                'instruction' => 'Klik untuk follow @batikumrah dan ambil screenshot',
                'link' => 'https://www.instagram.com/batikumrah?igsh=MTFibTFtOHF3dGp4MQ==',
                'is_required' => true,
            ];
        }
        if ($this->meta['proof_review_required'] ?? true) {
            $proofs[] = [
                'id' => 'proof_review',
                'label' => 'Bukti Google Review',
                'instruction' => 'Isi Google Review lalu ambil screenshot',
                'link' => 'https://bit.ly/googlereviewbatik',
                'is_required' => true,
            ];
        }
        return $proofs;
    }

    /**
     * Get maximum tickets allowed per transaction based on tenant or event setting.
     */
    public function getMaxTicketsPerTransaction(): int
    {
        // 1. Cek pengaturan tenant jika ada (organizer/settings/terms -> max_tickets_per_person)
        if (!empty($this->tenant?->meta['max_tickets_per_person'])) {
            return max(1, (int) $this->tenant->meta['max_tickets_per_person']);
        }

        // 2. Cek pengaturan batas tiket pada event
        if (!empty($this->max_tickets_per_transaction)) {
            return max(1, (int) $this->max_tickets_per_transaction);
        }

        return 2;
    }

    public function isFinished(): bool
    {
        return $this->status === 'finished';
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'sport' => 'Olahraga & Pertandingan',
            'festival' => 'Festival & Expo',
            'seminar' => 'Seminar & Workshop',
            'other' => 'Lainnya',
            default => 'Konser Musik',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'sport' => '⚽',
            'festival' => '🎪',
            'seminar' => '🎓',
            'other' => '✨',
            default => '🎵',
        };
    }

    /**
     * Get the attendee gender setting: 'disabled', 'general', or 'islamic'.
     */
    public function getAttendeeGenderSetting(): string
    {
        if (isset($this->meta['attendee_gender_type'])) {
            return $this->meta['attendee_gender_type'];
        }

        // Backward compatibility: If legacy umroh question was enabled, default to 'islamic'
        if ($this->umroh_question_enabled) {
            return 'islamic';
        }

        return 'general';
    }

    /**
     * Get the customizable transaction questions for participants.
     */
    public function getCustomFields(): array
    {
        if (isset($this->meta['custom_fields']) && is_array($this->meta['custom_fields']) && !empty($this->meta['custom_fields'])) {
            return array_map(function ($f) {
                return [
                    'id' => $f['id'] ?? ('cf_' . \Illuminate\Support\Str::random(6)),
                    'label' => $f['label'] ?? '',
                    'type' => $f['type'] ?? 'text',
                    'placeholder' => $f['placeholder'] ?? '',
                    'helper_text' => $f['helper_text'] ?? '',
                    'options' => $f['options'] ?? [],
                    'is_required' => !empty($f['is_required']),
                    'show_in_evoucher' => isset($f['show_in_evoucher']) ? (bool) $f['show_in_evoucher'] : true,
                ];
            }, $this->meta['custom_fields']);
        }

        if ($this->umroh_question_enabled || !empty($this->meta['custom_question_text'])) {
            $label = $this->meta['custom_question_text'] ?? 'Pertanyaan Custom';
            $type = $this->meta['custom_question_type'] ?? 'text';
            $options = $this->meta['custom_question_options'] ?? [];
            $helper = $this->meta['custom_question_helper'] ?? ($label === 'Alumni Grup Keberangkatan Tanggal Berapa?' ? 'Jika lebih dari 1X, Maka bisa diisi keberangkatan paling terakhir' : '');
            $placeholder = $this->meta['custom_question_placeholder'] ?? ($label === 'Alumni Grup Keberangkatan Tanggal Berapa?' ? 'Contoh: 13 Januari 2024' : '');

            return [
                [
                    'id' => 'umroh_answer',
                    'label' => $label,
                    'type' => $type,
                    'placeholder' => $placeholder,
                    'helper_text' => $helper,
                    'options' => $options,
                    'is_required' => false,
                    'show_in_evoucher' => true,
                ]
            ];
        }

        return [];
    }
}

