<?php

declare(strict_types=1);
use Rector\Config\RectorConfig;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withParallel()
    ->withPreparedSets(codeQuality: true)
    ->withPhpSets(php82: true)
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/*.php',
    ])
    ->withSets([
        SymfonySetList::SYMFONY_62,
    ])
;
