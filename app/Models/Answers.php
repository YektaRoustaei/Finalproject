<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answers extends Model
{
    use HasFactory;

    protected $fillable = [
        'seeker_id',
        'question_id',
        'job_id',
        'answer',
    ];

    /**
     * Get the seeker that owns the answer.
     */
    public function seeker()
    {
        return $this->belongsTo(Seeker::class);
    }

    /**
     * Get the questionnaire that owns the answer.
     */
    public function questionnaire()
    {
        return $this->belongsTo(Questionnaire::class, 'question_id');
    }
}
