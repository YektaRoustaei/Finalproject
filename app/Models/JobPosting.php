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
        'provider_id',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'job_categories', 'job_id', 'category_id');
    }

    public function jobskills()
    {
        return $this->hasMany(JobSkill::class)->with('skill');    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }
}
