<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasFactory;

    protected $fillable = [
        'curriculum_vitae_id',
        'institution',
        'degree',
        'field_of_study',
        'start_date',
        'end_date',
    ];

    public function curriculumVitae()
    {
        return $this->belongsTo(CurriculumVitae::class, 'curriculum_vitae_id', 'id');
    }
}
