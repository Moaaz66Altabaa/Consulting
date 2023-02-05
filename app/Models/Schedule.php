<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = ['expert_id' , 'isAvailable' , 'day' , 'start' , 'end'];

    public function expert(){
        return $this->belongsTo(Expert::class);
    }
}
