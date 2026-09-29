<?php

namespace App\Command;

use App\Database\Database;
use App\Database\SqlFileRunner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'db:status', description: 'Show migration and seeder status.')]
final class DatabaseStatusCommand extends Command
{
    protected function configure(): void
    {
        $this->setAliases(['status']);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $runner = new SqlFileRunner(Database::connect());

        $this->renderSection(
            $io,
            'Migrations',
            $runner->status(getenv('MIGRATIONS_PATH') ?: '/var/www/db/migrations', 'schema_migrations')
        );

        $this->renderSection(
            $io,
            'Seeders',
            $runner->status(getenv('SEEDS_PATH') ?: '/var/www/db/seeds', 'schema_seeds')
        );

        return Command::SUCCESS;
    }

    /**
     * @param list<array{filename: string, state: string}> $rows
     */
    private function renderSection(SymfonyStyle $io, string $title, array $rows): void
    {
        $io->section($title);
        $io->table(['State', 'File'], array_map(
            static fn (array $row): array => [$row['state'], $row['filename']],
            $rows
        ));
    }
}
