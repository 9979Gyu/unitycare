<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $table = 'applications';
    protected $primaryKey = 'application_id';

    protected $fillable = [
        'application_id',
        'applied_date',
        'offer_id',
        'poor_id',
        'approved_by',
        'approved_at',
        'approval_status',
        'status',
        'description',
    ];

    public function jobOffer()
    {
        return $this->belongsTo(Job_Offer::class, 'offer_id');
    }

    public function poor()
    {
        return $this->belongsTo(Poor::class, 'poor_id');
    }
}
