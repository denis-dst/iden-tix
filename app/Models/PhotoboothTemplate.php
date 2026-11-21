<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoboothTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'event_id',
        'name',
        'is_active',
        'frame_overlay',
        'background_image',
        'primary_color',
        'secondary_color',
        'header_title',
        'header_subtitle',
        'badge_text',
        'category_label_format',
        'footer_text',
        'custom_watermark',
        'show_qr',
        'show_fan_name',
        'show_category',
        'show_match_info',
        'show_team_logos',
        'layout_config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_qr' => 'boolean',
        'show_fan_name' => 'boolean',
        'show_category' => 'boolean',
        'show_match_info' => 'boolean',
        'show_team_logos' => 'boolean',
        'layout_config' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
