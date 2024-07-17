<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_posting_id',
        'requirement',
    ];

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }
}
