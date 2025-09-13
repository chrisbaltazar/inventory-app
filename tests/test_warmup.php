<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;

$kernel = new Kernel('test', true);
$kernel->boot();
$container = $kernel->getContainer();
$application = new Application($kernel);
$application->setAutoExit(false);

$fs = new Filesystem();
$databasePath = $container->getParameter('kernel.project_dir') . '/var/data_test.db';
if (!$fs->exists($databasePath)) {
    // Drop database if exists
    $application->run(new ArrayInput([
        'command' => 'doctrine:database:drop',
        '--env' => 'test',
        '--force' => true,
        '--if-exists' => true,
    ]), new NullOutput());

    // Create database
    $application->run(new ArrayInput([
        'command' => 'doctrine:database:create',
        '--env' => 'test',
    ]), new NullOutput());

    // Create schema
    $application->run(new ArrayInput([
        'command' => 'doctrine:schema:create',
        '--env' => 'test',
    ]), new NullOutput());

    // Load fixtures
    $application->run(new ArrayInput([
        'command' => 'doctrine:fixtures:load',
        '--env' => 'test',
        '--no-interaction' => true,
    ]), new NullOutput());
}

$kernel->shutdown();