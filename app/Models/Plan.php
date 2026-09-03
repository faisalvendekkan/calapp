<?php

namespace App\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'price', 'currency', 'billing_interval', 'limits', 'features', 'is_active'];

    protected $casts = ['limits' => 'array', 'features' => 'array', 'is_active' => 'boolean'];
}
