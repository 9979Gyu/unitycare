<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'description',
        'venue',
        'type_id',
        'user_id',
        'approved_by',
        'approved_at',
        'approved_status',
        'status',
        'close_date',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function programSpecs(){
        return $this->hasMany(Program_Spec::class);
    }



}
