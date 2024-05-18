<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job_Offer extends Model
{
    use HasFactory;

    protected $table = 'job_offers';

    protected $fillable = [
        'offer_id',
        'job_id',
        'description',
        'user_id',
        'start_date',
        'end_date',
        'salary_range',
        'address',
        'state',
        'city',
        'postal_code',
        'sector_id',
    ];

}
