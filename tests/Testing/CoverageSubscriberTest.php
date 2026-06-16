<?php

declare(strict_types=1);

namespace Ubermuda\SymfonyExtra\Tests\Testing;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Ubermuda\SymfonyExtra\Testing\CoverageSubscriber;

final class CoverageSubscriberTest extends TestCase
{
    public function test_subscribes_to_request_and_terminate(): void
    {
        $events = CoverageSubscriber::getSubscribedEvents();

        self::assertArrayHasKey(KernelEvents::REQUEST, $events);
        self::assertArrayHasKey(KernelEvents::TERMINATE, $events);
    }

    public function test_does_nothing_without_the_coverage_header(): void
    {
        $outputDir = sys_get_temp_dir().'/'.uniqid('cov_test_', true);
        $subscriber = new CoverageSubscriber(__DIR__, $outputDir);

        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();

        $subscriber->onKernelRequest(new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST));
        $subscriber->onKernelTerminate(new TerminateEvent($kernel, $request, new Response()));

        self::assertDirectoryDoesNotExist($outputDir);
    }
}
