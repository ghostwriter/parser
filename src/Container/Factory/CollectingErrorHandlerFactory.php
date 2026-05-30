<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Container\Factory;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;
use Override;
use PhpParser\ErrorHandler;
use PhpParser\ErrorHandler\Collecting;
use Throwable;

/**
 * @see ErrorHandlerFactoryTest
 *
 * @implements FactoryInterface<ErrorHandler>
 */
final readonly class CollectingErrorHandlerFactory implements FactoryInterface
{
    /** @throws Throwable */
    #[Override]
    public function __invoke(ContainerInterface $container): ErrorHandler
    {
        return new Collecting();
    }
}
