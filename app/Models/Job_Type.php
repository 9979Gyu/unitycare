<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job_Type extends Model
{
    use HasFactory;

    protected $table = 'job_types';

    protected $fillable = [
        'job_type_id',
        'name',
        'description',
        'status',
    ];
}
