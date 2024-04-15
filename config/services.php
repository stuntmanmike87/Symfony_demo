<?php

declare(strict_types=1);

use App\EventSubscriber\CommentNotificationSubscriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('locale', 'en');

    // $parameters->set('app_locales', 'ar|en|fr|de|es|cs|nl|ru|uk|ro|pt_BR|pl|it|ja|id|ca|sl|sq|hr|zh_CN|bg|tr|lt|bs|sr_Cyrl|sr_Latn|eu|ne|bn|vi');

    $parameters->set('app.notifications.email_sender', 'anonymous@example.com');

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        // ->bind('string $locales', '%app_locales%')
        ->bind('array $enabledLocales', '%kernel.enabled_locales%')
        ->bind('string $defaultLocale', '%locale%')
        ->bind('string $emailSender', '%app.notifications.email_sender%');

    $services->load('App\\', __DIR__ . '/../src/')
        ->exclude([
        __DIR__ . '/../src/DependencyInjection/',
        __DIR__ . '/../src/Entity/',
        __DIR__ . '/../src/Kernel.php',
    ]);

    $services->set(CommentNotificationSubscriber::class)
        ->arg('$sender', '%app.notifications.email_sender%');

    //$services->alias(LogoutUrlGenerator::class, 'security.logout_url_generator');
};
