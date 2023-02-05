<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expert extends Model
{
    use HasFactory;

    protected $fillable = ['user_id' , 'category_id' , 'expertDescription' , 'hourPrice' , 'rate'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
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

}
