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
        'min_salary',
        'max_salary',
        'state',
        'city',
        'postal_code',
        'job_type_id',
        'shift_type_id',
        'approval_status',
        'status',
    ];

}
