<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $table = 'applications';

    protected $fillable = [
        'applied_date',
        'offer_id',
        'poor_id',
        'approved_by',
        'approved_at',
        'approval_status',
        'status',
    ];
}
