<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction_Type extends Model
{
    use HasFactory;

    protected $table = 'transaction_types';
    protected $primaryKey = 'transaction_type_id';

    protected $fillable = [
        'transaction_type_id',
        'name',
        'status',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'transaction_type_id');
    }

}
