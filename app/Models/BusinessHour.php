<?php

namespace App\Models;

use Database\Factories\BusinessHourFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessHour extends Model
{
    /** @use HasFactory<BusinessHourFactory> */
    use HasFactory;

    protected $fillable = ['profile_id', 'weekday', 'opens_at', 'closes_at', 'is_closed', 'is_24_hours'];
}
