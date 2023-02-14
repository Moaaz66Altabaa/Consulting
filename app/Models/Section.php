<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function experts(){
        return $this->hasMany(Expert::class);
    }
}
