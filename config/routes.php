<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->add('homepage', '/{_locale}')
        ->controller('Symfony\Bundle\FrameworkBundle\Controller\TemplateController::templateAction')
        ->defaults([
        'template' => 'default/homepage.html.twig',
        '_locale' => '%app.locale%',
    ])
/*         ->requirements([
        '_locale' => '%app_locales%',
    ]) */;

    $routingConfigurator->import([
        'path' => '../src/Controller/',
        'namespace' => 'App\Controller',
    ], 'attribute')
        ->prefix('/{_locale}')
        ->defaults([
        '_locale' => '%app.locale%',
    ])
/*         ->requirements([
        '_locale' => '%app_locales%',
    ]) */;
};
