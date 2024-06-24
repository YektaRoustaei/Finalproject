<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppliedJob extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\AppliedJobFactory::new();
    }

    protected $fillable = [
        'job_id',
        'seeker_id',
    ];
}
