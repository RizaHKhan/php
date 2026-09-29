<?php

namespace App\Command;

use App\Database\Database;
use App\Database\SqlFileRunner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'db:migrate', description: 'Apply pending SQL migrations.')]
final class MigrateCommand extends Command
{
    protected function configure(): void
    {
        $this->setAliases(['migrate']);
    }

    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = getenv('MIGRATIONS_PATH') ?: '/var/www/db/migrations';
        $runner = new SqlFileRunner(Database::connect());
        $pending = $runner->pending($path, 'schema_migrations');

        if ($pending === []) {
            $io->success('No pending migrations.');
            return Command::SUCCESS;
        }

        foreach ($pending as $filename) {
            $io->writeln("Applying {$filename}...");
        }

        $runner->run($path, 'schema_migrations');
        $io->success('Migrations complete.');

        return Command::SUCCESS;
    }
}
