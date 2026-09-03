<?php

namespace App\Models;

use Database\Factories\ContactMethodFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMethod extends Model
{
    /** @use HasFactory<ContactMethodFactory> */
    use HasFactory;

    protected $fillable = ['profile_id', 'type', 'label', 'value', 'url', 'is_visible', 'position'];
}
