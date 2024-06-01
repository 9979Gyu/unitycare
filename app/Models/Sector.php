<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    use HasFactory;

    protected $table = 'sectors';
    protected $primaryKey = 'sector_id';

    protected $fillable = [
        'sector_id',
        'name',
        'status',
    ];

    public function organizations()
    {
        return $this->hasMany(User::class, 'sector_id');
    }
}
