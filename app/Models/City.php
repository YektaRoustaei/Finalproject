<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_name',
        'Latitude',
        'Longitude',

    ];

    public function seekers()
    {
        return $this->hasMany(Seeker::class);
    }

    public function provider()
    {
        return $this->hasMany(Provider::class);
    }



}
