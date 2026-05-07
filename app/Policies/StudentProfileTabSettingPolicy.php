<?php

namespace App\Policies;

use App\Models\StudentProfileTabSetting;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class StudentProfileTabSettingPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $this->canManage($authUser, 'ViewAny:StudentProfileTabSetting');
    }

    public function view(AuthUser $authUser, StudentProfileTabSetting $studentProfileTabSetting): bool
    {
        return $this->canManage($authUser, 'View:StudentProfileTabSetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $this->canManage($authUser, 'Create:StudentProfileTabSetting');
    }

    public function update(AuthUser $authUser, StudentProfileTabSetting $studentProfileTabSetting): bool
    {
        return $this->canManage($authUser, 'Update:StudentProfileTabSetting');
    }

    public function delete(AuthUser $authUser, StudentProfileTabSetting $studentProfileTabSetting): bool
    {
        return $this->canManage($authUser, 'Delete:StudentProfileTabSetting');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $this->canManage($authUser, 'DeleteAny:StudentProfileTabSetting');
    }

    public function restore(AuthUser $authUser, StudentProfileTabSetting $studentProfileTabSetting): bool
    {
        return $this->canManage($authUser, 'Restore:StudentProfileTabSetting');
    }

    public function forceDelete(AuthUser $authUser, StudentProfileTabSetting $studentProfileTabSetting): bool
    {
        return $this->canManage($authUser, 'ForceDelete:StudentProfileTabSetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $this->canManage($authUser, 'ForceDeleteAny:StudentProfileTabSetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $this->canManage($authUser, 'RestoreAny:StudentProfileTabSetting');
    }

    public function replicate(AuthUser $authUser, StudentProfileTabSetting $studentProfileTabSetting): bool
    {
        return $this->canManage($authUser, 'Replicate:StudentProfileTabSetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $this->canManage($authUser, 'Reorder:StudentProfileTabSetting');
    }

    private function canManage(AuthUser $authUser, string $permission): bool
    {
        return (method_exists($authUser, 'hasRole') && $authUser->hasRole('super_admin'))
            || $authUser->can($permission);
    }
}
