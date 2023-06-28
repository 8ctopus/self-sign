<?php

declare(strict_types=1);

namespace Oct8pus\SelfSign;

use Exception;
use Oct8pus\SelfSign\CommandGenerate;
use Symfony\Component\Console\Application;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    throw new Exception('autoload not found');
}

$app = new Application('self-sign', '0.1.0');

$app->add(new CommandGenerate());

$app->run();
