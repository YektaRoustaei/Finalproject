<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobExperience extends Model
{
    use HasFactory;

    protected $fillable = [
        'curriculum_vitae_id',
        'company_name',
        'position',
        'start_date',
        'end_date',
        'description',
    ];

    public function curriculumVitae()
    {
        return $this->belongsTo(CurriculumVitae::class);
    }
}
