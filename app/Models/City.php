<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_name',
        'Latitude',
        'Longitude',

    ];

    public function seekers()
    {
        return $this->hasMany(Seeker::class);
    }

    public function provider()
    {
        return $this->hasMany(Provider::class);
    }

    public function jobPostings()
    {
        return $this->hasManyThrough(JobPosting::class, Provider::class, 'city_id', 'provider_id');
    }

    public function appliedJobs()
    {
        return $this->hasManyThrough(AppliedJob::class, Seeker::class, 'city_id', 'seeker_id');
    }
    public function providers()
    {
        return $this->hasMany(Provider::class);
    }



}
