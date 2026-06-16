<?php

declare(strict_types=1);

namespace Ubermuda\SymfonyExtra\Testing;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * Disables the Symfony profiler (and thus the Web Debug Toolbar) for all
 * requests carrying the X-Playwright header, sent by the Playwright e2e suite
 * via extraHTTPHeaders.
 *
 * When profiling is disabled, no toolbar HTML is injected and the WDT's
 * background XHR (/_wdt/TOKEN) never fires — eliminating the PHP session
 * write-lock race that causes CSRF failures under concurrent test load.
 */
final readonly class DisableProfilerForPlaywrightSubscriber implements EventSubscriberInterface
{
    public const string HEADER = 'X-Playwright';

    public function __construct(
        private ?Profiler $profiler,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (null === $this->profiler || !$event->isMainRequest()) {
            return;
        }

        if ($event->getRequest()->headers->has(self::HEADER)) {
            $this->profiler->disable();
        }
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => 'onKernelRequest'];
    }
}
