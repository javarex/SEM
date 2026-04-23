<?php

namespace App\Traits;

use Spatie\Permission\Traits\HasRoles;

trait HasUserRole
{
    use HasRoles;

    public function isAdmin(bool $super = false): bool
    {
        return $super ? $this->hasRole('super_admin') : $this->hasAnyRole(['super_admin', 'pswdo']);
    }
}
