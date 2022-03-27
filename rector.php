<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\LevelSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Laravel\Rector\ClassMethod\AddArgumentDefaultValueRector;
use Rector\Laravel\Rector\ClassMethod\AddParentRegisterToEventServiceProviderRector;
use Rector\Laravel\Rector\MethodCall\RemoveAllOnDispatchingMethodsWithJobChainingRector;
use Rector\Laravel\ValueObject\AddArgumentDefaultValue;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameProperty;

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

    $services = $containerConfigurator->services();

    # https://github.com/laravel/framework/commit/4d228d6e9dbcbd4d97c45665980d8b8c685b27e6
    $services->set(ArgumentAdderRector::class)
        ->configure([new ArgumentAdder(
            'Illuminate\Contracts\Database\Eloquent\Castable',
            'castUsing',
            0,
            'arguments',
            [], // TODO: Add argument without default value
                    new ArrayType(new MixedType(), new MixedType())
        ),
        ]);

    # https://github.com/laravel/framework/commit/46084d946cdcd1ae1f32fc87a4f1cc9e3a5bccf6
    $services->set(AddArgumentDefaultValueRector::class)
        ->configure([new AddArgumentDefaultValue('Illuminate\Contracts\Events\Dispatcher', 'listen', 1, null)]);

    # https://github.com/laravel/framework/commit/f1289515b27e93248c09f04e3011bb7ce21b2737
    $services->set(AddParentRegisterToEventServiceProviderRector::class);

    $services->set(RenamePropertyRector::class)
        ->configure([                # https://github.com/laravel/framework/pull/32092/files
            new RenameProperty('Illuminate\Support\Manager', 'app', 'container'),
            # https://github.com/laravel/framework/commit/4656c2cf012ac62739ab5ea2bce006e1e9fe8f09
            new RenameProperty('Illuminate\Contracts\Queue\ShouldQueue', 'retryAfter', 'backoff'),
            # https://github.com/laravel/framework/commit/12c35e57c0a6da96f36ad77f88f083e96f927205
            new RenameProperty('Illuminate\Contracts\Queue\ShouldQueue', 'timeoutAt', 'retryUntil'),
        ]);

    $services->set(RenameMethodRector::class)
        ->configure([                # https://github.com/laravel/framework/pull/32092/files
            new MethodCallRename('Illuminate\Mail\PendingMail', 'sendNow', 'send'),
            # https://github.com/laravel/framework/commit/4656c2cf012ac62739ab5ea2bce006e1e9fe8f09
            new MethodCallRename('Illuminate\Contracts\Queue\ShouldQueue', 'retryAfter', 'backoff'),
            # https://github.com/laravel/framework/commit/12c35e57c0a6da96f36ad77f88f083e96f927205
            new MethodCallRename('Illuminate\Contracts\Queue\ShouldQueue', 'timeoutAt', 'retryUntil'),
            # https://github.com/laravel/framework/commit/f9374fa5fb0450721fb2f90e96adef9d409b112c
            new MethodCallRename('Illuminate\Testing\TestResponse', 'decodeResponseJson', 'json'),
            # https://github.com/laravel/framework/commit/fd662d4699776a94e7ead2a42e82c340363fc5a6
            new MethodCallRename('Illuminate\Testing\TestResponse', 'assertExactJson', 'assertSimilarJson'),
        ]);

    # https://github.com/laravel/framework/commit/de662daf75207a8dd69565ed3630def74bc538d3
    $services->set(RemoveAllOnDispatchingMethodsWithJobChainingRector::class);
};
