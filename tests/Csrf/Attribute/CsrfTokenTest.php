<?php

declare(strict_types=1);

namespace Ubermuda\SymfonyExtra\Tests\Csrf\Attribute;

use PHPUnit\Framework\TestCase;
use Ubermuda\SymfonyExtra\Csrf\Attribute\CsrfToken;

final class CsrfTokenTest extends TestCase
{
    public function test_defaults_parameter_name(): void
    {
        $attribute = new CsrfToken('my-token');

        self::assertSame('my-token', $attribute->tokenId);
        self::assertSame('_csrf_token', $attribute->parameter);
    }

    public function test_custom_parameter_name(): void
    {
        $attribute = new CsrfToken('my-token', '_token');

        self::assertSame('_token', $attribute->parameter);
    }
}
