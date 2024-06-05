<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education_Level extends Model
{
    use HasFactory;

    protected $table = 'education_levels';
    protected $primaryKey = 'edu_level_id';

    protected $fillable = [
        'name',
        'status',
    ];

    public function poors()
    {
        return $this->hasMany(Poor::class, 'education_level');
    }
}
