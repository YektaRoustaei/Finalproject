<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function jobPostings()
    {
        return $this->belongsToMany(JobPosting::class, 'job_categories', 'category_id', 'job_id');
    }
}
