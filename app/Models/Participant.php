<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $table = 'participants';
    protected $primaryKey = 'participant_id';

    protected $fillable = [
        'program_id',
        'user_type_id',
        'user_id',
        'status',
    ];

    public function program(){
        return $this->belongsTo(Program::class, 'program_id');
    }
}
