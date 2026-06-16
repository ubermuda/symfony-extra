<?php

declare(strict_types=1);

namespace Ubermuda\SymfonyExtra\Testing;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Serialization\Serializer as CoverageSerializer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Collects PHP code coverage for requests carrying the X-Coverage header,
 * writing one serialized .cov file per request to the configured output
 * directory. Enables end-to-end coverage from browser-driven (Playwright) test
 * runs; the .cov files are merged into a report afterwards.
 *
 * Requires the sebastian/code-coverage package and the xdebug extension; both
 * are no-ops when absent, so this subscriber is harmless when coverage is off.
 */
final class CoverageSubscriber implements EventSubscriberInterface
{
    public const string HEADER = 'X-Coverage';

    private ?CodeCoverage $coverage = null;

    public function __construct(
        private readonly string $sourceDir,
        private readonly string $outputDir,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$event->getRequest()->headers->has(self::HEADER)) {
            return;
        }

        if (!extension_loaded('xdebug')) {
            return;
        }

        $filter = new Filter();
        $excludedFiles = [
            __FILE__,
        ];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->sourceDir));
        foreach ($files as $file) {
            if (!$file->isFile() || 'php' !== $file->getExtension()) {
                continue;
            }
            $path = $file->getRealPath();
            foreach ($excludedFiles as $excludedFile) {
                if ($path === $excludedFile) {
                    continue 2;
                }
            }
            $filter->includeFile($path);
        }

        $this->coverage = new CodeCoverage(new Selector()->forLineCoverage($filter), $filter);
        $this->coverage->start('request');
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        if (null === $this->coverage) {
            return;
        }

        $this->coverage->stop();

        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0777, true);
        }

        new CoverageSerializer()->serialize($this->outputDir.'/'.uniqid('cov_', true).'.cov', $this->coverage);

        $this->coverage = null;
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 0],
            KernelEvents::TERMINATE => ['onKernelTerminate', 0],
        ];
    }
}
