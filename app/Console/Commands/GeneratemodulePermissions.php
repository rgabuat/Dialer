<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PermissionRegistrar;

class GenerateModulePermissions extends Command
{
    protected $signature = 'permissions:generate {module}';
    protected $description = 'Generate permissions for a Livewire module';

    public function handle(): int
    {
        $module = strtolower($this->argument('module'));

        PermissionRegistrar::for($module);

        $this->info("Permissions generated for module: {$module}");

        return self::SUCCESS;
    }
}
