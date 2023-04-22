<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    public $timestamps = false;

    public function universityCourses(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(UniversityCourse::class);
    }

}
