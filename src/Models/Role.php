<?php
namespace AbdAlrzaq\Roles\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name'];
    // App\Models\User.php
    public function roleables()
    {
        return $this->morphedByMany(Role::class, 'roleable');
    }
}
