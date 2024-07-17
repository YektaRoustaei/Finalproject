<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppliedJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'seeker_id',
    ];

    protected static function newFactory()
    {
        return \Database\Factories\SavedJobFactory::new();
    }

    public function seeker()
    {
        return $this->belongsTo(Seeker::class);
    }
}
