<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift_Type extends Model
{
    use HasFactory;

    protected $table = 'shift_types';
    protected $primaryKey = 'shift_type_id';

    protected $fillable = [
        'shift_type_id',
        'name',
        'description',
        'status',
    ];

    public function jobOffers()
    {
        return $this->hasMany(Job_Offer::class, 'shift_type_id');
    }
}
