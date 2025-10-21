<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Registrar comando de migração de roles antigas para novas
Artisan::command('roles:migrate {--dry-run}', function () {
    /** @var \App\Console\Commands\MigrarRolesAntigasCommand $command */
    $command = app(\App\Console\Commands\MigrarRolesAntigasCommand::class);
    // Passa a opção --dry-run se fornecida
    $input = new \Symfony\Component\Console\Input\ArrayInput([
        '--dry-run' => $this->option('dry-run'),
    ]);
    $output = new \Symfony\Component\Console\Output\ConsoleOutput();
    $command->setLaravel(app());
    $command->run($input, $output);
})->purpose('Migrar usuários das roles antigas para as novas');
