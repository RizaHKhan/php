#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Command\DatabaseStatusCommand;
use App\Command\MigrateCommand;
use App\Command\SeedCommand;
use Symfony\Component\Console\Application;

$application = new Application('PHP App Database Console');
$application->addCommands([
    new MigrateCommand(),
    new SeedCommand(),
    new DatabaseStatusCommand(),
]);
$application->setDefaultCommand('list');
$application->run();
