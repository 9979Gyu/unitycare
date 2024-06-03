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
        'volunteer_id'
    ];

    public function oku()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
