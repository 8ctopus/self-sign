<?php

declare(strict_types=1);

namespace Oct8pus\SelfSign;

use Exception;
use Symfony\Component\Console\Application;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    throw new Exception('autoload not found');
}

$app = new Application('self-sign', '0.1.8');

$app->add(new CommandAuthority());
$app->add(new CommandCertificate());

$app->run();
