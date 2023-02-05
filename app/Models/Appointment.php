<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id' , 'expert_id' , 'from' , 'to'];

    public function users(){
        return $this->belongsTo(User::class);
    }

    public function experts(){
        return $this->belongsTo(Expert::class);
    }
}
