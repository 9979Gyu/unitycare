<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poor extends Model
{
    use HasFactory;

    protected $fillable = [
        'disability_type',
        'education_level',
        'instituition_name',
        'employment_status',
        'status',
        'user_id',
        'volunteer_id'
    ];
}
