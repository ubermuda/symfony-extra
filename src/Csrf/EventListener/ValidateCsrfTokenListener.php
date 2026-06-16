<?php

declare(strict_types=1);

namespace Ubermuda\SymfonyExtra\Csrf\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken as SecurityCsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Ubermuda\SymfonyExtra\Csrf\Attribute\CsrfToken;

/**
 * Validates the #[CsrfToken] attribute before the controller runs.
 *
 * Throws AccessDeniedException (→ 403) when the submitted token is missing or
 * invalid.
 */
#[AsEventListener]
final readonly class ValidateCsrfTokenListener
{
    public function __construct(
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    public function __invoke(ControllerEvent $event): void
    {
        // Sub-requests (forward(), fragments) never carry the user's form
        // submission — only validate the main request.
        if (!$event->isMainRequest()) {
            return;
        }

        foreach ($event->getAttributes(CsrfToken::class) as $attribute) {
            $token = new SecurityCsrfToken(
                $attribute->tokenId,
                $event->getRequest()->request->getString($attribute->parameter),
            );

            if (!$this->csrfTokenManager->isTokenValid($token)) {
                throw new AccessDeniedException('Invalid CSRF token.');
            }
        }
    }
}
