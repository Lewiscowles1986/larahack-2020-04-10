<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\LevelSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# see https://laravel.com/docs/7.x/upgrade
return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [
        __DIR__ . '/app',
        __DIR__ . '/bootstrap',
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/database',
        __DIR__ . '/resources/lang',
        __DIR__ . '/resources/views',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ]);
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_80);
    $containerConfigurator->import(LevelSetList::UP_TO_PHP_80);
};
