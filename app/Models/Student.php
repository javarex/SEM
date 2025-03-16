<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $guarded = [];

    protected function fullname(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->last_name.' '.$this->first_name,
        );
    }
//    protected function totalScore(): Attribute
//    {
//        return Attribute::make(
//            get: fn () => $this->last_name.' '.$this->first_name,
//        );
//    }

    public function scores(): HasMany
    {
        return $this->hasMany(StudentScore::class);
    }

    public function score(): HasOne
    {
        return $this->hasOne(StudentScore::class);
    }

}
