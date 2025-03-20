<?php

namespace App\Traits;

use Spatie\Permission\Traits\HasRoles;

trait HasUserRole
{
    use HasRoles;

    public function isAdmin($super = false)
    {
        return $super ? $this->hasRole('super_admin') : $this->hasAnyRole(['super_admin', 'pswdo']);
    }
}
