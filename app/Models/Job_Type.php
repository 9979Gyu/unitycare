<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job_Type extends Model
{
    use HasFactory;

    protected $table = 'job_types';
    protected $primaryKey = 'job_type_id';

    protected $fillable = [
        'job_type_id',
        'name',
        'description',
        'status',
    ];

    public function jobOffers()
    {
        return $this->hasMany(Job_Offer::class, 'job_type_id');
    }
}
