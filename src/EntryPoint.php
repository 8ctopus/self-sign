<?php

declare(strict_types=1);

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    require __DIR__ . '/vendor/autoload.php';
}

$app = new Symfony\Component\Console\Application('self-sign', '0.1.0');

$app->add(new Oct8pus\SelfSign\CommandGenerate());

$app->run();
