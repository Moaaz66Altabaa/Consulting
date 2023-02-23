<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use SebastianBergmann\Type\VoidType;


class Expert extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guard = 'experts_api';

    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function section(){
        return $this->belongsTo(Section::class);
    }

    public function experiences(){
        return $this->hasMany(Experience::class);
    }

    public function schedules(){
        return $this->hasMany(Schedule::class);
    }

    public function ratings(){
        return $this->hasMany(Rating::class);
    }

    public function appointments(){
        return $this->hasMany(Appointment::class);
    }

    public function wallet(){
        return $this->hasOne(ExpertWallet::class);
    }

}
