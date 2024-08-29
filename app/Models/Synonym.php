<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Synonym extends Model
{
    use HasFactory;

    protected $table = 'synonyms';

    protected $fillable = [
        'title',
        'synonym1',
        'synonym2',
        'synonym3',
        'synonym4',
        'synonym5',
    ];
}
