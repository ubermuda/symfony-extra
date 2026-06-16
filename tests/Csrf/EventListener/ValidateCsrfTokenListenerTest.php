<?php

declare(strict_types=1);

namespace Ubermuda\SymfonyExtra\Tests\Csrf\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Ubermuda\SymfonyExtra\Csrf\EventListener\ValidateCsrfTokenListener;
use Ubermuda\SymfonyExtra\Tests\Csrf\Fixtures\GuardedController;

final class ValidateCsrfTokenListenerTest extends TestCase
{
    private function event(int $requestType): ControllerEvent
    {
        return new ControllerEvent(
            $this->createStub(HttpKernelInterface::class),
            new GuardedController(),
            new Request(request: ['_csrf_token' => 'submitted']),
            $requestType,
        );
    }

    private function listener(bool $tokenValid): ValidateCsrfTokenListener
    {
        $manager = $this->createStub(CsrfTokenManagerInterface::class);
        $manager->method('isTokenValid')->willReturn($tokenValid);

        return new ValidateCsrfTokenListener($manager);
    }

    public function test_invalid_token_is_rejected(): void
    {
        $this->expectException(AccessDeniedException::class);

        ($this->listener(tokenValid: false))($this->event(HttpKernelInterface::MAIN_REQUEST));
    }

    public function test_valid_token_passes(): void
    {
        ($this->listener(tokenValid: true))($this->event(HttpKernelInterface::MAIN_REQUEST));

        $this->expectNotToPerformAssertions();
    }

    public function test_sub_requests_are_not_validated(): void
    {
        // An invalid token would throw on a main request; a sub-request must skip.
        ($this->listener(tokenValid: false))($this->event(HttpKernelInterface::SUB_REQUEST));

        $this->expectNotToPerformAssertions();
    }
}
