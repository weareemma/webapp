<?php

namespace App\Console\Commands\BitBoss\Permissions;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CreateRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bit:make:role {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new Role with the Spatie Permissions package';

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
        $name = $this->argument('name');
        if (!$name) $name = $this->ask('What is the role name?');

        Role::firstOrCreate(['name' => $name]);

        $this->info("Role \"$name\" created");
    }
}
