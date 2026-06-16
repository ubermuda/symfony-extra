<?php

declare(strict_types=1);

namespace Ubermuda\SymfonyExtra\Tests\Csrf\Fixtures;

use Ubermuda\SymfonyExtra\Csrf\Attribute\CsrfToken;

#[CsrfToken('test-token')]
final class GuardedController
{
    public function __invoke(): void
    {
    }
}
