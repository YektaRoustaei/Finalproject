<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function seekerSkills()
    {
        return $this->hasMany(SeekerSkill::class);
    }
    public function jobSkills()
    {
        return $this->hasMany(JobSkill::class);
    }

}
