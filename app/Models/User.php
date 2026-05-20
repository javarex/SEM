<?php

namespace App\Models;

use App\Traits\HasUserRole;
use App\UserTeam;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    use HasUserRole;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'team',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'team' => UserTeam::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() !== 'admin') {
            return false;
        }

        return $this->hasAnyRole([
            'super_admin',
            'pswdo',
            'pswdo_admin',
            'panelist',
            'panel_user',
        ]);
    }

    public function studentScores(): HasMany
    {
        return $this->hasMany(StudentScore::class);
    }

    public function scores(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_scores', 'user_id', 'student_id')
            ->withTimestamps();
    }

    public function superAdmin(): Attribute
    {
        return new Attribute(
            get: fn () => $this->roles->contains('name', 'super_admin')
        );
    }

    public function pswdoAdmin(): Attribute
    {
        return new Attribute(
            get: fn () => $this->hasAnyRole(['super_admin', 'pswdo_admin'])
        );
    }
}
