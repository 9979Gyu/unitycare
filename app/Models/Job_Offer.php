<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job_Offer extends Model
{
    use HasFactory;

    protected $table = 'job_offers';
    protected $primaryKey = 'offer_id';

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

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    public function organization()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jobType()
    {
        return $this->belongsTo(Job_Type::class, 'job_type_id');
    }

    public function shiftType()
    {
        return $this->belongsTo(Shift_Type::class, 'shift_type_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'offer_id');
    }

}
