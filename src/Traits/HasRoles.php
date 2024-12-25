<?php

namespace AbdAlrzaq\Roles\Traits;

use AbdAlrzaq\Roles\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @method morphToMany(string $class, string $string, string $string1)
 */
trait HasRoles
{
    public function roles()
    {
        return $this->morphToMany(Role::class, 'model', 'model_role');
    }

    /**
     * @throws \Exception
     */
    public function assignRole($role): void
    {
        if ($this->hasRole($role)) return;

        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }

        $roles = $this->roles()->where('root', $role->root)->get();
        foreach ($roles as $current_role) {
            if ($this->is_ancestor($role, $current_role)) {
                $this->roles()->detach($current_role);
            }
        }
        $this->roles()->syncWithoutDetaching($role);
    }
    /**
     * @throws \Exception
     */
    public function assignRoles($roles): void
    {
        DB::beginTransaction();
        try {
            foreach ($roles as $role) {
                $this->assignRole($role);
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function removeRole($role): void
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        $this->roles()->detach($role);
    }

    public function hasRole($role): bool|int
    {

        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        $roles = $this->roles()->where('root', $role->root)->get();
        $exist = false;
        foreach ($roles as $current_role) {
            $exist |= $this->is_ancestor($current_role, $role);
        }
        return $exist;

    }


    function is_ancestor($current_role, $required_role): bool
    {
        return $required_role->entry <= $current_role->entry && $required_role->exit >= $current_role->exit;
    }


}