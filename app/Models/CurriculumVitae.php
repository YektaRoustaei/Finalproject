<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurriculumVitae extends Model
{
    use HasFactory;

    protected $casts = [
        'created_at'  => 'date:m-d-Y',
        'updated_at'  => 'date:m-d-Y',
    ];

    protected $fillable = [
        'seeker_id',
    ];

    public function seeker()
    {
        return $this->belongsTo(Seeker::class);
    }

    public function seekerSkills()
    {
        return $this->hasMany(SeekerSkill::class);
    }


    public function educations()
    {
        return $this->hasMany(Education::class, 'curriculum_vitae_id', 'id');
    }

    public function jobExperiences()
    {
        return $this->hasMany(JobExperience::class);
    }
}
