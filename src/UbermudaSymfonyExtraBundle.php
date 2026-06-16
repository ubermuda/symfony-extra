<?php

declare(strict_types=1);

namespace Ubermuda\SymfonyExtra;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class UbermudaSymfonyExtraBundle extends AbstractBundle
{
    #[\Override]
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('coverage')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('source_dir')
                            ->info('Directory whose .php files are included in coverage.')
                            ->defaultValue('%kernel.project_dir%/src')
                        ->end()
                        ->scalarNode('output_dir')
                            ->info('Directory where per-request .cov files are written.')
                            ->defaultValue('%kernel.project_dir%/var/coverage')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array{coverage: array{source_dir: string, output_dir: string}} $config
     */
    #[\Override]
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->setParameter('ubermuda_symfony_extra.coverage.source_dir', $config['coverage']['source_dir']);
        $builder->setParameter('ubermuda_symfony_extra.coverage.output_dir', $config['coverage']['output_dir']);

        $container->import(__DIR__.'/../config/services.php');
    }
}
