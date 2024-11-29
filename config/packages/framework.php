<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('framework', [
        'secret' => '%env(APP_SECRET)%',
        // 'csrf_protection' => true,
        'annotations' => false,
        'http_method_override' => false,
        // 'handle_all_throwables' => true,
        'enabled_locales' => ['ar', 'bg', 'bn', 'bs', 'ca', 'cs', 'de', 'en', 'es', 'eu', 'fr', 'hr', 'id', 'it', 'ja', 'lt', 'ne', 'nl', 'pl', 'pt_BR', 'ro', 'ru', 'sl', 'sq', 'sr_Cyrl', 'sr_Latn', 'tr', 'uk', 'vi', 'zh_CN'],
        // 'session' => [
        //     'handler_id' => null,
        //     'cookie_secure' => 'auto',
        //     'cookie_samesite' => 'lax',
        // ],
        'session' => true,
        'esi' => true,
        'fragments' => true,
        // 'php_errors' => [
        //     'log' => true,
        // ],
        'ide' => null,
    ]);
    if ($containerConfigurator->env() === 'test') {
        $containerConfigurator->extension('framework', [
            'test' => true,
            'session' => [
                'storage_factory_id' => 'session.storage.factory.mock_file',
            ],
        ]);
    }
};
