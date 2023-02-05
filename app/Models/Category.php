<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['categoryName' , 'imagePath'];

    public function experts(){
        return $this->hasMany(Expert::class);
    }
}
