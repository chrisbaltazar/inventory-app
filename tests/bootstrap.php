<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;

require dirname(__DIR__) . '/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

$kernel = new Kernel('test', true);
$kernel->boot();
$container = $kernel->getContainer();
$application = new Application($kernel);
$application->setAutoExit(false);

// Only run this if using SQLite (adjust if needed for your DB engine)
$fs = new Filesystem();
$databasePath = $container->getParameter('kernel.project_dir') . '/var/data_test.db';
if (!$fs->exists($databasePath)) {
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