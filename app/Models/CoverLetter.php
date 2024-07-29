<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoverLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'seeker_id',
        'content',
        'pdf_path',
    ];

    public function seeker()
    {
        return $this->belongsTo(Seeker::class);
    }
}
