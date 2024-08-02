<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'seeker_id',
        'status',
    ];

    const STATUS_NOT_INTERESTED = 'not interested';

    public function seeker()
    {
        return $this->belongsTo(Seeker::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($jobAlert) {
            if ($jobAlert->status !== self::STATUS_NOT_INTERESTED) {
                throw new \InvalidArgumentException('Invalid status');
            }
        });
    }
}
