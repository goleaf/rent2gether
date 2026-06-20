<?php

namespace App\Console\Commands;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:demo-reset {--seed-only : Seed demo data without rebuilding the schema} {--force : Allow the reset in production}')]
#[Description('Reset the database to the lightweight demo marketplace dataset')]
class ResetDemoData extends Command
{
    public function handle(): int
    {
        if (app()->isProduction() && ! $this->option('force')) {
            $this->components->error(__('app.demo_reset.production_blocked'));

            return self::FAILURE;
        }

        if ($this->option('seed-only')) {
            $this->components->info(__('app.demo_reset.seeding'));
            $this->call('db:seed', [
                '--class' => DatabaseSeeder::class,
                '--force' => true,
            ]);
        } else {
            $this->components->info(__('app.demo_reset.fresh'));
            $this->call('migrate:fresh', [
                '--seed' => true,
                '--force' => true,
            ]);
        }

        $this->components->info(__('app.demo_reset.complete'));

        return self::SUCCESS;
    }
}
