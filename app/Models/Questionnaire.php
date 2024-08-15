<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Questionnaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'question',
        'answer_type',
        'min_value',
        'max_value',
    ];

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class, 'job_id');
    }
    public function answers()
    {
        return $this->hasMany(Answers::class, 'question_id');
    }
}
