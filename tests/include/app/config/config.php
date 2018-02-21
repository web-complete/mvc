<?php

use Symfony\Component\Filesystem\Filesystem;
use WebComplete\core\utils\cache\CacheService;

return [
    'aliases' => [
        '@app' => \dirname(__DIR__ . '/..', 2),
        '@web' => \dirname(__DIR__ . '/../web', 2),
    ],
    'routes' => new \WebComplete\mvc\router\Routes([
        ['GET', '/some/string', [\tests\app\controllers\SomeController::class, 'actionString']],
        ['GET', '/some/layout', [\tests\app\controllers\SomeController::class, 'actionLayout']],
        ['GET', '/some/partial', [\tests\app\controllers\SomeController::class, 'actionPartial']],
        ['GET', '/some/json', [\tests\app\controllers\SomeController::class, 'actionJson']],
        ['GET', '/some/array', [\tests\app\controllers\SomeController::class, 'actionArray']],
        ['GET', '/some/redirect', [\tests\app\controllers\SomeController::class, 'actionRedirect']],
        ['GET', '/some/not-found', [\tests\app\controllers\SomeController::class, 'actionNotFound']],
        ['GET', '/some/access-denied', [\tests\app\controllers\SomeController::class, 'actionAccessDenied']],
        ['GET', '/some/system-error', [\tests\app\controllers\SomeController::class, 'actionSystemError']],
        ['POST', '/some/only-post', [\tests\app\controllers\SomeController::class, 'actionOnlyPost']],
        ['GET', '/some/vars', [\tests\app\controllers\SomeController::class, 'actionVars']],
        ['GET', '/some/fail1', [null, 'index']],
        ['GET', '/some/fail2', [\tests\app\controllers\SomeController::class, null]],
    ]),
    'cubesLocations' => [
        '@app/cubes',
    ],
    'definitions' => [
        'errorController' => \DI\autowire(\tests\app\controllers\ErrorController::class),
        \Psr\SimpleCache\CacheInterface::class => \DI\autowire(\Symfony\Component\Cache\Simple\NullCache::class),
        \WebComplete\mvc\assets\AssetManager::class => function (\DI\Container $di) {
            $aliasService = $di->get(\WebComplete\core\utils\alias\AliasService::class);
            return new \WebComplete\mvc\assets\AssetManager(
                new Filesystem(),
                $aliasService->get('@web'),
                'assets'
            );
        },
        CacheService::class => function () {
            $systemCache = new \Symfony\Component\Cache\Adapter\NullAdapter();
            $userCache = new \Symfony\Component\Cache\Adapter\NullAdapter();
            return new CacheService($systemCache, $userCache);
        },
    ],
    'errorPagePath' => '/some/error',
];
