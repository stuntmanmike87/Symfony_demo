<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import('../../src/Controller/', 'annotation')
        ->prefix('/{_locale}')
        ->defaults([
        '_locale' => '%locale%',
    ])
        ->requirements([
        '_locale' => '%app_locales%',
    ]);
};
