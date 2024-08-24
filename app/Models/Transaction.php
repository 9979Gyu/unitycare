<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    protected $primaryKey = 'transaction_id';

    protected $fillable = [
        'transaction_id',
        'payer_name',
        'amount',
        'currency',
        'payment_status',
        'references',
        'reference_no',
        'receiver_id',
        'payer_id',
        'transaction_type_id',
        'created_at',
    ];

    public function transactionType()
    {
        return $this->belongsTo(Transaction_Type::class, 'transaction_type_id');
    }

    public function userPay()
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function userReceive()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
