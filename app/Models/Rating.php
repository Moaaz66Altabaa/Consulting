<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = ['expert_id' , 'user_id' , 'starsNumber'];

    public function expert(){
        return $this->belongsTo(Expert::class);
    }
}
