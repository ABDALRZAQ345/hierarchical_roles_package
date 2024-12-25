<?php
namespace AbdAlrzaq\Roles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name','parent_id','root','entry','exit'];
    public function parent():BelongsTo
    {
        return $this->belongsTo(Role::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Role::class, 'parent_id');
    }


    protected static function booted(): void
    {
        static::saved(function ($role) {
            // Set the root for the role
            if ($role->parent_id) {
                $role->root = $role->parent->root ?? $role->parent_id;
            } else {
                $role->root = $role->id;
            }
            $role->saveQuietly();

            // Perform the Euler tour update
            $rootRole = Role::find($role->root);
            if ($rootRole) {
                $time = 0;
                self::eulerTour($rootRole, $time); // Use self:: instead of $this
            }
        });
    }

    protected static function eulerTour($role, &$time = 0): void
    {
        // Set the entry time
        $role->entry = ++$time;
        $role->saveQuietly();

        // Recursive call for all children
        foreach ($role->children as $child) {
            self::eulerTour($child, $time); // Use self:: for the recursive call
        }

        // Set the exit time
        $role->exit = ++$time;
        $role->saveQuietly();
    }




}
