<?php
namespace AbdAlrzaq\Roles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Exception;
use Illuminate\Support\Facades\DB;

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
        static::deleted(function ($role) {

            $rootRole = Role::find($role->root);
            if ($rootRole) {
                $time = 0;
                self::eulerTour($rootRole, $time);
            }
        });
    }

    protected static function eulerTour($role, &$time = 0): void
    {
        $role=self::resolveRole($role);
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


    public function assignChilds($roles): void
    {
        
            $roles = is_array($roles) ? $roles : [$roles];
            foreach ($roles as $role) {
                self::assignChild($role);
            }

    }



    /**
     * @throws \Exception
     */
    public function assignChild($role): void
    {
        $role=self::resolveRole($role);
       
        if(!$role){
            throw new Exception('no such role');
        }
        
        $current=Role::find($this->id);
        if($current->root == $role->root){
            if(self::is_ancestor($role,$current)) {
                throw new Exception('circular roles');
            }
        }
        
        if($role->root!=null ){
            Role::where('root',$role->root)->update(['root'=>$this->root]);
        }
        $role->parent()->associate($this->id);
        $role->save();
        self::eulerTour($role->root);
    }
    protected static function hasCircularDependency($current, $role): bool
    {
       

        while ($current != null) {
            if ($current->id == $role->id) {
                return true;
            }
            $current = $current->parent; // Move up the hierarchy
        }

        return false;
    }

    protected static function resolveRole($role): ?Role
    {
        if (is_string($role)) {
            return Role::where('name', $role)->first();
        } elseif (is_int($role)) {
            return Role::find($role);
        } elseif ($role instanceof Role) {
            return Role::find($role->id);
        }
        return null;
    }

    public function getHierarchy(): array
    {
        $tree = [];
        foreach ($this->children as $child) {
            $child->getHierarchy();
            $tree[] = [
                'role' => $child,
            ];
        }
        return $tree;
    }

    protected static function rebuildEulerTour(): void
    {
        $roots = Role::whereNull('parent_id')->get();
        $time = 0;
        foreach ($roots as $root) {
            self::eulerTour($root, $time);
        }
    }
    protected static function is_ancestor($first, $second): bool
    {
        if (is_null($first->entry) || is_null($first->exit) || is_null($second->entry) || is_null($second->exit)) {
            return false;
        }
        return $first->entry <= $second->entry && $first->exit >= $second->exit;
    }

}
