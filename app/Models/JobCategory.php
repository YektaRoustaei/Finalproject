<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCategory extends Model
{

    protected $table = 'job_categories';
    use HasFactory;
    protected $fillable = ['job_id', 'category_id'];
}
