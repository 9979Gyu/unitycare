<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $table = 'programs';
    protected $primaryKey = 'program_id';

    protected $fillable = [
        'program_id',
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
        'state',
        'city',
        'postal_code'
    ];

    public function organization(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function programSpecs(){
        return $this->hasMany(Program_Spec::class, 'program_id');
    }

    public function participants(){
        return $this->hasMany(Participant::class, 'program_id');
    }

}
