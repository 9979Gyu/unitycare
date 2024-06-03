<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program_Spec extends Model
{
    use HasFactory;

    protected $table = 'program_specs';
    protected $primaryKey = 'spec_id';

    protected $fillable = [
        'program_id',
        'user_type_id',
        'qty_limit',
        'qty_enrolled',
    ];

    public function program(){
        return $this->belongsTo(Program::class, 'program_id');
    }

}
