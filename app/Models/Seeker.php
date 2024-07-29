<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Seeker extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phonenumber',
        'password',
        'city_id',
    ];

    protected static function newFactory()
    {
        return \Database\Factories\SeekerFactory::new();
    }

    public function appliedJobs()
    {
        return $this->hasMany(AppliedJob::class);
    }

    public function savedJobs()
    {
        return $this->hasMany(SavedJob::class);
    }
    public function curriculumVitae()
    {
        return $this->hasMany(CurriculumVitae::class,'seeker_id');
    }
    public function coverLetter()
    {
        return $this->hasMany(CoverLetter::class,'seeker_id');
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
