<?php

namespace App\Console\Commands\BitBoss\Permissions;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GivePermissionToRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bit:bind:permission {permissionName} {roleName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gives a permission to a specific role';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $permissionName = $this->argument('permissionName');
        $roleName = $this->argument('roleName');

        $permission = Permission::where('name', $permissionName)->first();
        if(!$permission){
            $this->error("Permission \"$permissionName\" not found");
            return;
        }

        $role = Role::where('name', $roleName)->first();
        if(!$role){
            $this->error("Role \"$roleName\" not found");
            return;
        }

        $role->givePermissionTo($permission);

        $this->info("Permission \"$permissionName\" binded to \"$roleName\" role");
    }
}
