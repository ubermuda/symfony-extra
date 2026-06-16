<?php

declare(strict_types=1);

namespace Ubermuda\SymfonyExtra\Csrf\Attribute;

/**
 * Declarative CSRF validation for controllers that handle hand-rolled forms
 * (plain HTML <form> elements not bound to a FormType).
 *
 * Place on the controller class (or its __invoke method); the token id must be
 * registered under framework.csrf_protection.stateless_token_ids and the
 * template must emit the signed token with the csrf_token() Twig function.
 * Validation happens in ValidateCsrfTokenListener before the action runs.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final readonly class CsrfToken
{
    public function __construct(
        public string $tokenId,
        public string $parameter = '_csrf_token',
    ) {
    }
}
