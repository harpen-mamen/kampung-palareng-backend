<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $table = 'site_settings';

    protected $fillable = [
        'hero_badge',
        'hero_title',
        'hero_description',
        'hero_primary_label',
        'hero_primary_url',
        'hero_secondary_label',
        'hero_secondary_url',
        'hero_panel_title',
        'hero_panel_description',
        'official_name',
        'official_position',
        'official_message',
        'hero_image',
        'hero_images',
        'hero_sections',
        'official_photo',
        'profile_title',
        'profile_description',
        'profile_history',
        'profile_vision_mission',
        'profile_potential',
        'profile_image',
        'government_structure',
        'surat_templates',
        'surat_numbering',
    ];

    protected $casts = [
        'hero_images' => 'array',
        'hero_sections' => 'array',
        'government_structure' => 'array',
        'surat_templates' => 'array',
        'surat_numbering' => 'array',
    ];
}
