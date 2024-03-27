<?php

namespace App\Console\Commands\BitBoss\Permissions;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class GiveRoleToUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bit:bind:role {roleName} {userEmail}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gives a role to a specific user';

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
        $roleName = $this->argument('roleName');
        $userEmail = $this->argument('userEmail');

        $role = Role::where('name', $roleName)->first();
        if(!$role){
            $this->error("Role \"$roleName\" not found");
            return;
        }

        $user = User::where('email', $userEmail)->first();
        if(!$user){
            $this->error("User \"$userEmail\" not found");
            return;
        }

        $user->assignRole($role);

        $this->info("Role \"$roleName\" assigned to \"$userEmail\" user");
    }
}
