<?php

declare(strict_types=1);

namespace Ubermuda\SymfonyExtra\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ubermuda\SymfonyExtra\Testing\CoverageSubscriber;
use Ubermuda\SymfonyExtra\UbermudaSymfonyExtraBundle;

final class UbermudaSymfonyExtraBundleTest extends TestCase
{
    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function environments(): iterable
    {
        yield 'dev' => ['dev', true];
        yield 'test' => ['test', true];
        yield 'prod' => ['prod', false];
    }

    #[DataProvider('environments')]
    public function test_registers_the_coverage_subscriber_only_in_dev_and_test(string $env, bool $registered): void
    {
        $builder = $this->load($env, []);

        self::assertSame($registered, $builder->hasDefinition(CoverageSubscriber::class));
    }

    public function test_cache_dir_defaults_to_the_phpunit_coverage_cache(): void
    {
        $builder = $this->load('test', []);

        self::assertSame(
            '%kernel.project_dir%/.phpunit.cache/code-coverage',
            $builder->getParameter('ubermuda_symfony_extra.coverage.cache_dir'),
        );
        self::assertSame(
            '%ubermuda_symfony_extra.coverage.cache_dir%',
            (string) $builder->getDefinition(CoverageSubscriber::class)->getArgument('$cacheDir'),
        );
    }

    public function test_cache_dir_can_be_disabled(): void
    {
        $builder = $this->load('test', ['coverage' => ['cache_dir' => null]]);

        self::assertNull($builder->getParameter('ubermuda_symfony_extra.coverage.cache_dir'));
    }

    /**
     * @param array<string, mixed> $config
     */
    private function load(string $env, array $config): ContainerBuilder
    {
        $builder = new ContainerBuilder();
        $builder->setParameter('kernel.environment', $env);
        $builder->setParameter('kernel.project_dir', '/app');
        $builder->setParameter('kernel.debug', false);
        $builder->setParameter('kernel.build_dir', sys_get_temp_dir());

        $extension = new UbermudaSymfonyExtraBundle()->getContainerExtension();
        self::assertNotNull($extension);
        $extension->load([$config], $builder);

        return $builder;
    }
}
