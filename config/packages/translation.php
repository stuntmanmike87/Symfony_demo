<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('framework', [
        'default_locale' => '%app.locale%',
        'translator' => [
            'default_path' => '%kernel.project_dir%/translations',
            'fallbacks' => [
                '%app.locale%',
            ],
            'providers' => [
                'crowdin' => [
                    'dsn' => '%env(CROWDIN_DSN)%',
                ],
                'loco' => [
                    'dsn' => '%env(LOCO_DSN)%',
                ],
                'lokalise' => [
                    'dsn' => '%env(LOKALISE_DSN)%',
                ],
                'phrase' => [
                    'dsn' => '%env(PHRASE_DSN)%',
                ],
            ],
        ],
    ]);
};
