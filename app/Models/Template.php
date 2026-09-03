<?php

namespace App\Models;

use Database\Factories\TemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    /** @use HasFactory<TemplateFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'component', 'default_configuration', 'supported_sections', 'is_active', 'is_premium'];

    protected $casts = ['default_configuration' => 'array', 'supported_sections' => 'array', 'is_active' => 'boolean', 'is_premium' => 'boolean'];
}
