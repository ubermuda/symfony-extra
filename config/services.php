<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Ubermuda\SymfonyExtra\Csrf\EventListener\ValidateCsrfTokenListener;
use Ubermuda\SymfonyExtra\Testing\CoverageSubscriber;
use Ubermuda\SymfonyExtra\Testing\DisableProfilerForPlaywrightSubscriber;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure();

    $services->set(ValidateCsrfTokenListener::class);

    // Symfony does not expose Profiler as an autowiring type, so the subscriber's
    // ?Profiler argument would silently resolve to null and the subscriber no-ops.
    // Wire the optional profiler service explicitly (null in prod where it is absent).
    $services->set(DisableProfilerForPlaywrightSubscriber::class)
        ->arg('$profiler', service('profiler')->nullOnInvalid());

    $services->set(CoverageSubscriber::class)
        ->arg('$sourceDir', param('ubermuda_symfony_extra.coverage.source_dir'))
        ->arg('$outputDir', param('ubermuda_symfony_extra.coverage.output_dir'));
};
