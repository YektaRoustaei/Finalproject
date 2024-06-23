<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'provider_id',
        'salary',
        'type',
        'location', // Ensure this is fillable
    ];
    protected static function booted()
    {
        static::creating(function ($job) {
            $job->location = $job->provider->location;
        });
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'job_categories');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
