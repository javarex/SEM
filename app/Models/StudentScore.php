<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentScore extends Model
{
    use SoftDeletes;

    public const EDITABLE_FIELDS = [
        'emotional',
        'intelligence',
        'socio_economic',
        'remarks',
    ];

    protected $guarded = [];

    /**
     * @return array<int, string>
     */
    public static function editableFields(): array
    {
        return self::EDITABLE_FIELDS;
    }

    public function isEditableBy(Authenticatable $user): bool
    {
        return (string) $this->user_id === (string) $user->getAuthIdentifier()
            && ($this->created_at === null || $this->created_at->isToday());
    }

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

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
