<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function Symfony\Component\String\u;

/**
 * When visiting the homepage, this listener redirects the user to the most
 * appropriate localized version according to the browser settings.
 *
 * See https://symfony.com/doc/current/components/http_kernel.html#the-kernel-request-event
 *
 * @author Oleg Voronkovich <oleg-voronkovich@yandex.ru>
 */
final class RedirectToPreferredLocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        /** @var string[] */
        private array $enabledLocales,
        private ?string $defaultLocale = null,
    ) {
        if ([] === $this->enabledLocales) {
            throw new \UnexpectedValueException('The list of supported locales must not be empty.');
        }

        // Only booleans are allowed in a ternary operator condition, string|null given.
        // $this->defaultLocale = $defaultLocale ?: $this->locales[0];
        // Short ternary operator is not allowed.
        // Use null coalesce operator if applicable or consider using long ternary.
        $this->defaultLocale = $defaultLocale ?? $this->enabledLocales[0];
        $m = 'The default locale ("%s") must be one of "%s".';
        $e = \sprintf($m, $this->defaultLocale, implode(', ', $this->enabledLocales));
        if (!\in_array($this->defaultLocale, $this->enabledLocales, true)) {
            throw new \UnexpectedValueException($e);
        }

        // Add the default locale at the first position of the array,
        // because Symfony\HttpFoundation\Request::getPreferredLanguage
        // returns the first element when no an appropriate language is found
        array_unshift($this->enabledLocales, $this->defaultLocale);
        $this->enabledLocales = array_unique($this->enabledLocales);
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Ignore sub-requests and all URLs but the homepage
        if (!$event->isMainRequest() || '/' !== $request->getPathInfo()) {
            return;
        }

        // Ignore requests from referrers with the same HTTP host in order to prevent
        // changing language for users who possibly already selected it for this application.
        $referrer = $request->headers->get('referer');

        if (null !== $referrer && u($referrer)->ignoreCase()->startsWith($request->getSchemeAndHttpHost())) {
            return;
        }

        $preferredLanguage = $request->getPreferredLanguage($this->enabledLocales);

        if ($preferredLanguage !== $this->defaultLocale) {
            $response = new RedirectResponse($this->urlGenerator->generate('homepage', ['_locale' => $preferredLanguage]));
            $event->setResponse($response);
        }
    }
}
