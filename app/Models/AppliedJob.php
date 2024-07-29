<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppliedJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'seeker_id',
        'status',
        'curriculum_vitae_id',
        'cover_letter_id'
    ];

    protected static function newFactory()
    {
        return \Database\Factories\AppliedJobFactory::new();
    }

    public function seeker()
    {
        return $this->belongsTo(Seeker::class);
    }

    public function curriculumVitae()
    {
        return $this->belongsTo(CurriculumVitae::class);
    }

    public function coverLetter()
    {
        return $this->belongsTo(CoverLetter::class); // Assuming you have a CoverLetter model
    }

    // Accessor for status attribute
    public function setStatusAttribute($value)
    {
        $validStatuses = ['accepted', 'hold', 'rejected'];

        if (!in_array($value, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid status value.");
        }

        $this->attributes['status'] = $value;
    }
}
