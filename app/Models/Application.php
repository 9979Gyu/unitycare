<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $table = 'job_offers';

    protected $fillable = [
        'applied_at',
        'offer_id',
        'poor_id',
    ];
}
