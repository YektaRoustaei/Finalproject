<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeekerSkill extends Model
{
    use HasFactory;

    protected $fillable = [
        'curriculum_vitae_id',
        'skill_id',
    ];

    public function curriculumVitae()
    {
        return $this->belongsTo(CurriculumVitae::class);
    }

    public function Skill()
    {
        return $this->belongsTo(Skill::class);
    }
}
