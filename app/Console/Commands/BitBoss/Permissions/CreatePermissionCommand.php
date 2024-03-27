<?php

namespace App\Console\Commands\BitBoss\Permissions;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class CreatePermissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bit:make:permission {name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new Permisison with the Spatie Permissions package';

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
        if (!$name) $name = $this->ask('What is the permission name?');

        Permission::firstOrCreate(['name' => $name]);

        $this->info("Permission \"$name\" created");
    }
}
