<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'username',
        'contactNo',
        'address',
        'state',
        'city',
        'postalCode',
        'officeNo',
        'ICNo',
        'status',
        'roleID',
        'sector_id',
        'remember_token',
        'email_verified_at',
        'image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function programs(){
        return $this->hasMany(Program::class, 'user_id');
    }

    public function jobOffers()
    {
        return $this->hasMany(Job_Offer::class, 'user_id');
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sector_id');
    }

    public function poor()
    {
        return $this->hasOne(Poor::class, 'user_id');
    }

    public function pay()
    {
        return $this->hasMany(Transaction::class, 'payer_id');
    }

    public function receive()
    {
        return $this->hasMany(Transaction::class, 'receiver_id');
    }

}
