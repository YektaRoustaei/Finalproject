<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Provider extends Authenticatable
{
    use HasFactory,HasApiTokens;
    protected $fillable = [
        'company_name',
        'description',
        'address',
        'telephone',
        'email',
        'password',
    ];
}
