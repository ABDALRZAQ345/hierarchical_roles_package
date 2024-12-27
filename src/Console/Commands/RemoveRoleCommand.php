<?php
namespace AbdAlrzaq\Roles\Console\Commands;

use AbdAlrzaq\Roles\Models\Role;
use Illuminate\Console\Command;


class RemoveRoleCommand extends Command
{
    protected $signature = 'roles:remove 
                            {model : The model class (e.g., App\\Models\\User)} 
                            {role : The role name or ID} 
                            {--ids=* : The IDs of the models to remove the role from (comma-separated)}';

    protected $description = 'Remove a role from multiple models';

    public function handle()
    {
        $modelClass = $this->argument('model');
        $role = $this->argument('role');
        $ids = $this->option('ids');

        if (!class_exists($modelClass)) {
            $this->error("The model class $modelClass does not exist.");
            return;
        }

        $roleInstance = Role::where('name', $role)->orWhere('id', $role)->first();
        if (!$roleInstance) {
            $this->error("Role '$role' not found.");
            return;
        }

        if (empty($ids)) {
            $this->error("No IDs were provided.");
            return;
        }

        foreach ($ids as $id) {
            $model=$modelClass::find($id);
            if (!$model) {
                $this->error("Role '$role' not found.");
            }
            $model->detachRole($roleInstance->name);
        }

        $this->info("Role '{$roleInstance->name}' removed from models.");
    }
}
