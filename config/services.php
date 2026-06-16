<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Ubermuda\SymfonyExtra\Csrf\EventListener\ValidateCsrfTokenListener;
use Ubermuda\SymfonyExtra\Testing\CoverageSubscriber;
use Ubermuda\SymfonyExtra\Testing\DisableProfilerForPlaywrightSubscriber;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure();

    $services->set(ValidateCsrfTokenListener::class);

    $services->set(DisableProfilerForPlaywrightSubscriber::class);

    $services->set(CoverageSubscriber::class)
        ->arg('$sourceDir', param('ubermuda_symfony_extra.coverage.source_dir'))
        ->arg('$outputDir', param('ubermuda_symfony_extra.coverage.output_dir'));
};
