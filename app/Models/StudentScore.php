<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentScore extends Model
{
    protected $guarded = [];

    use SoftDeletes;

    protected function totalScore(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->emotional + $this->intelligence + $this->socio_economic,
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
