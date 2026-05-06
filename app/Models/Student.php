<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'municipality',
        'type',
        'sex',
        'purok',
        'barangay',
        'school',
        'family_background',
        'category',
        'ethnicity',
        'ranking',
        'exam_score',
        'pcro_remarks',
        'cao_remarks',
        'ydd_remarks',
        'fullname',
    ];

    public function scores(): HasMany
    {
        return $this->hasMany(StudentScore::class);
    }

    public function score(): HasOne
    {
        return $this->hasOne(StudentScore::class);
    }
}
