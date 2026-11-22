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

    /**
     * Get layout coordinates with smart defaults.
     */
    public function getEffectiveLayoutConfig(): array
    {
        $default = [
            'league_logo' => ['x' => 15, 'y' => 9, 'size' => 65, 'visible' => true],
            'header_slogan' => ['x' => 50, 'y' => 5, 'font_size' => 14, 'visible' => true],
            'home_logo' => ['x' => 24, 'y' => 69, 'size' => 85, 'visible' => true],
            'home_name' => ['x' => 24, 'y' => 77, 'font_size' => 14, 'visible' => true],
            'vs_badge' => ['x' => 50, 'y' => 74, 'font_size' => 22, 'visible' => false],
            'away_logo' => ['x' => 76, 'y' => 69, 'size' => 85, 'visible' => true],
            'away_name' => ['x' => 76, 'y' => 77, 'font_size' => 14, 'visible' => true],
            'fan_name' => ['x' => 50, 'y' => 86, 'font_size' => 18, 'visible' => true],
            'category_badge' => ['x' => 50, 'y' => 82, 'font_size' => 10, 'visible' => true],
            'match_info' => ['x' => 50, 'y' => 92, 'font_size' => 11, 'visible' => true],
            'qr_code' => ['x' => 88, 'y' => 92, 'size' => 48, 'visible' => true],
            'footer_text' => ['x' => 50, 'y' => 97, 'font_size' => 9, 'visible' => true],
        ];

        if (empty($this->layout_config) || !is_array($this->layout_config)) {
            return $default;
        }

        return array_replace_recursive($default, $this->layout_config);
    }
}
