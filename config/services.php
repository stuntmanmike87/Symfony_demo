<?php

declare(strict_types=1);

use App\EventSubscriber\CommentNotificationSubscriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('locale', 'en');

    $parameters->set('app_locales', 'ar|en|fr|de|es|cs|nl|ru|uk|ro|pt_BR|pl|it|ja|id|ca|sl|hr|zh_CN|bg|tr|lt|bs|sr_Cyrl|sr_Latn');

    $parameters->set('app.notifications.email_sender', 'anonymous@example.com');

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('string $locales', '%app_locales%')
        ->bind('string $defaultLocale', '%locale%')
        ->bind('string $emailSender', '%app.notifications.email_sender%');

    $services->load('App\\', __DIR__ . '/../src/')
        ->exclude([
        __DIR__ . '/../src/DependencyInjection/',
        __DIR__ . '/../src/Entity/',
        __DIR__ . '/../src/Kernel.php',
        __DIR__ . '/../src/Tests/',
    ]);

    $services->set(CommentNotificationSubscriber::class)
        ->arg('$sender', '%app.notifications.email_sender%');
    if ($containerConfigurator->env() === 'test') {
        $containerConfigurator->extension('services', [
            'test.user_password_hasher' => [
                'alias' => 'security.user_password_hasher',
                'public' => true,
            ],
        ]);
    }
};
