<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PermissionRegistrar;

class GenerateModulePermissions extends Command
{
    protected $signature = 'permissions:generate {module?} {--all : Generate for all models}';
    protected $description = 'Generate CRUD permissions for a module or all models';

    public function handle(): int
    {
        if ($this->option('all')) {
            $result = PermissionRegistrar::forAll();

            foreach ($result['created'] as $module) {
                $this->line("  <info>✔ created</info>  {$module}");
            }

            foreach ($result['skipped'] as $module) {
                $this->line("  <comment>- skipped</comment>  {$module}");
            }

            $this->info(count($result['created']) . ' created, ' . count($result['skipped']) . ' already existed.');

            return self::SUCCESS;
        }

        if (! $module = $this->argument('module')) {
            $this->error('Provide a module name or use --all.');
            return self::FAILURE;
        }

        PermissionRegistrar::for(strtolower($module));
        $this->info("Permissions generated for module: {$module}");

        return self::SUCCESS;
    }
}
