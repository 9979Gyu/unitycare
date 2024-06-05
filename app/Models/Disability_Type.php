<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disability_Type extends Model
{
    use HasFactory;

    protected $table = 'disability_types';
    protected $primaryKey = 'dis_type_id';

    protected $fillable = [
        'name',
        'status',
    ];

    public function poors()
    {
        return $this->hasMany(Poor::class, 'disability_type');
    }
}
