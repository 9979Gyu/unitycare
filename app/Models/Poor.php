<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poor extends Model
{
    use HasFactory;

    protected $table = 'poors';
    protected $primaryKey = 'poor_id';

    protected $fillable = [
        'poor_id',
        'disability_type',
        'education_level',
        'instituition_name',
        'employment_status',
        'status',
        'user_id',
        'volunteer_id',
        'resume',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function disabilityType()
    {
        return $this->belongsTo(Disability_Type::class, 'disability_type');
    }

    public function educationLevel()
    {
        return $this->belongsTo(Education_Level::class, 'education_level');
    }
}
