<?php

declare(strict_types=1);

namespace Ubermuda\SymfonyExtra\Tests\Testing;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Ubermuda\SymfonyExtra\Testing\DisableProfilerForPlaywrightSubscriber;

final class DisableProfilerForPlaywrightSubscriberTest extends TestCase
{
    private function requestEvent(bool $withHeader): RequestEvent
    {
        $request = new Request();
        if ($withHeader) {
            $request->headers->set(DisableProfilerForPlaywrightSubscriber::HEADER, '1');
        }

        return new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );
    }

    public function test_disables_profiler_when_header_present(): void
    {
        $profiler = $this->createMock(Profiler::class);
        $profiler->expects($this->once())->method('disable');

        new DisableProfilerForPlaywrightSubscriber($profiler)
            ->onKernelRequest($this->requestEvent(withHeader: true));
    }

    public function test_leaves_profiler_enabled_without_header(): void
    {
        $profiler = $this->createMock(Profiler::class);
        $profiler->expects($this->never())->method('disable');

        new DisableProfilerForPlaywrightSubscriber($profiler)
            ->onKernelRequest($this->requestEvent(withHeader: false));
    }

    public function test_no_op_when_profiler_is_absent(): void
    {
        new DisableProfilerForPlaywrightSubscriber(null)
            ->onKernelRequest($this->requestEvent(withHeader: true));

        $this->expectNotToPerformAssertions();
    }
}
