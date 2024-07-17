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
        'salary',
        'type',
        'location',
        'provider_id',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'job_categories', 'job_id', 'category_id');
    }

    public function requirements()
    {
        return $this->hasMany(JobRequirement::class);
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
