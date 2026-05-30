<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Container\Factory;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;
use Override;
use PhpParser\PhpVersion;
use Throwable;

/**
 * @see PhpVersionFactoryTest
 *
 * @implements FactoryInterface<PhpVersion>
 */
final readonly class PhpVersionFactory implements FactoryInterface
{
    /** @throws Throwable */
    #[Override]
    public function __invoke(ContainerInterface $container): PhpVersion
    {
        return PhpVersion::getNewestSupported();
    }
}
