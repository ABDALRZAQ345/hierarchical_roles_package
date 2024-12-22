<?php
namespace AbdAlrzaq\Roles\Traits;
use AbdAlrzaq\Roles\Models\Role;
use Illuminate\Support\Facades\DB;

trait HasRoles
{
    public function roles()
    {
        return $this->morphToMany(Role::class, 'roleable');
    }

    public function assignRole($role)
    {
        $roleInstance = $role instanceof Role ? $role : Role::where('name', $role)->firstOrFail();
        $this->roles()->syncWithoutDetaching($roleInstance->id);
    }
    public function assignRoles($roles)
    {
        DB::beginTransaction();
        try{

            foreach ($roles as $role) {
                $roleInstance = $role instanceof Role ? $role : Role::where('name', $role)->firstOrFail();
                $this->roles()->syncWithoutDetaching($roleInstance->id);
            }
            DB::commit();
        }
        catch (\Exception $exception){
            DB::rollBack();
        }


    }

    public function detachRole($role)
    {
        $roleInstance = $role instanceof Role ? $role : Role::where('name', $role)->firstOrFail();
        $this->roles()->detach($roleInstance->id);
    }

    public function hasRole($role)
    {
        return $this->roles->contains('name', $role);
    }
    public function hasAnyRole($roles)
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function hasAllRoles($roles)
    {
        $hasAllRoles = true;
        foreach ($roles as $role) {
            $hasAllRoles = ($hasAllRoles & $this->hasRole($role) );
        }
        return $hasAllRoles;
    }

}