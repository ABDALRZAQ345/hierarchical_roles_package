<?php
namespace AbdAlrzaq\Roles\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name'];
    // App\Models\User.php
    public function roles()
    {
        return $this->belongsToMany(\AbdAlrzaq\Roles\Models\Role::class);
    }
    public static function createRole(string $name): Role
    {
        return self::create(['name' => $name]);
    }

    // حذف دور
    public static function deleteRole(string $name): bool
    {
        $role = self::where('name', $name)->first();
        if ($role) {
            $role->delete();
            return true;
        }

        return false; // إذا لم يكن الدور موجودًا
    }
    public static function assignRoleToUser($user, string $roleName): bool
    {
        // البحث عن الدور أو إنشائه
        $role = self::firstOrCreate(['name' => $roleName]);

        // إضافة الدور للمستخدم
        if (!$user->roles->contains($role)) {
            $user->roles()->attach($role);
            return true;
        }

        return false;
    }

    // إزالة دور من مستخدم
    public static function removeRoleFromUser($user, string $roleName): bool
    {
        $role = self::where('name', $roleName)->first();

        if ($role && $user->roles->contains($role)) {
            $user->roles()->detach($role);
            return true;
        }

        return false;
    }
    public static function getAllRoles(): \Illuminate\Database\Eloquent\Collection
    {
        return Role::all();
    }

}
