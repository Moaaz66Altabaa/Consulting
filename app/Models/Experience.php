<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = ['expert_id' , 'experienceName' , 'experienceBody'];

    public function expert(){
        return $this->belongsTo(Expert::class);
    }
}
