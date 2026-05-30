<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Container\Factory;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;
use Ghostwriter\Parser\Printer;
use Override;
use PhpParser\PhpVersion;
use PhpParser\PrettyPrinter\Standard;
use Throwable;

/**
 * @see StandardFactoryTest
 *
 * @implements FactoryInterface<Standard>
 */
final readonly class PrinterFactory implements FactoryInterface
{
    /** @throws Throwable */
    #[Override]
    public function __invoke(ContainerInterface $container): Standard
    {
        return new Printer([
            'phpVersion' => $container->get(PhpVersion::class),
            'shortArraySyntax' => true,
            'newline' => "\n",
        ]);
    }
}
