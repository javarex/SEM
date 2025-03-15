<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class StudentScore extends Model
{
    protected $guarded = [];

    protected function totalScore(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->emotional + $this->intelligence + $this->socio_economic,
        );
    }
}
