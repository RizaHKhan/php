<?php

namespace App\Command;

use App\Database\Database;
use App\Database\SqlFileRunner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'db:seed', description: 'Apply pending SQL seeders.')]
final class SeedCommand extends Command
{
    protected function configure(): void
    {
        $this->setAliases(['seed']);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = getenv('SEEDS_PATH') ?: '/var/www/db/seeds';
        $runner = new SqlFileRunner(Database::connect());
        $pending = $runner->pending($path, 'schema_seeds');

        if ($pending === []) {
            $io->success('No pending seeders.');
            return Command::SUCCESS;
        }

        foreach ($pending as $filename) {
            $io->writeln("Applying {$filename}...");
        }

        $runner->run($path, 'schema_seeds');
        $io->success('Seeders complete.');

        return Command::SUCCESS;
    }
}
