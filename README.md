# ubermuda/symfony-extra

Extra cross-cutting Symfony infrastructure, extracted from
`ubermuda/symfony-skeleton`.

## Installation

```bash
composer require ubermuda/symfony-extra
```

Register the bundle (Symfony Flex does this automatically):

```php
// config/bundles.php
return [
    // ...
    Ubermuda\SymfonyExtra\UbermudaSymfonyExtraBundle::class => ['all' => true],
];
```

## `#[CsrfToken]` — declarative CSRF for hand-rolled forms

For plain HTML `<form>` elements not bound to a Symfony FormType, annotate the
controller (class or `__invoke`) with the token id:

```php
use Ubermuda\SymfonyExtra\Csrf\Attribute\CsrfToken;

#[CsrfToken('delete-account')]
final class DeleteAccountController extends AbstractController
{
    public function __invoke(): Response { /* ... */ }
}
```

`ValidateCsrfTokenListener` validates the submitted token before the action runs
and throws `AccessDeniedException` (→ 403) when it is missing or invalid.

Register the token id and emit it in the template, as usual for stateless CSRF:

```yaml
# config/packages/csrf.yaml
framework:
    csrf_protection:
        stateless_token_ids:
            - delete-account
```

```twig
<input type="hidden" name="_csrf_token" value="{{ csrf_token('delete-account') }}">
```

The attribute's second argument overrides the request parameter name (default
`_csrf_token`).

## Playwright e2e test-support

These subscribers are keyed off request headers your Playwright suite sends, so
they are inert in normal traffic.

### `DisableProfilerForPlaywrightSubscriber`

Disables the Symfony profiler / Web Debug Toolbar for requests carrying
`X-Playwright`, removing the WDT background XHR that races on the PHP session
lock under concurrent test load. Set the header for every request:

```ts
// playwright.config.ts
use: { extraHTTPHeaders: { 'X-Playwright': '1' } }
```

### `CoverageSubscriber`

Collects PHP line coverage for requests carrying `X-Coverage`, writing one
`.cov` file per request for later merging. Requires `phpunit/php-code-coverage`
and the `xdebug` extension. Without them, the subscriber does nothing. The bundle
registers it in the `dev` and `test` environments only.

Each `.cov` file holds only the source files that the request ran. Merge it with
one baseline file that lists every source file, so that the report still counts
the files that no request reached.

The subscriber reads its static analysis from `cache_dir`. Fill that cache once
before the test run with `XDEBUG_MODE=coverage vendor/bin/phpunit --warm-coverage-cache`.
With an empty cache, each request parses the files it ran. Set `cache_dir` to
`null` to switch the cache off.

The directories default as below. Override them if needed:

```yaml
# config/packages/ubermuda_symfony_extra.yaml
ubermuda_symfony_extra:
    coverage:
        source_dir: '%kernel.project_dir%/src'
        output_dir: '%kernel.project_dir%/var/coverage'
        cache_dir: '%kernel.project_dir%/.phpunit.cache/code-coverage'
```

## License

MIT
