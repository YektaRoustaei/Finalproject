<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Future extends Model
{
    use HasFactory;

    protected $fillable = [
        'curriculum_vitae_id',
        'provider_id',
        'seeker_id',
    ];

    /**
     * Get the curriculum vitae associated with the next step.
     */
    public function curriculumVitae()
    {
        return $this->belongsTo(CurriculumVitae::class);
    }
    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }
}
