<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Container\Factory;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;
use Override;
use PhpParser\Parser;
use PhpParser\PhpVersion;
use Throwable;

/**
 * @see ParserFactoryTest
 *
 * @implements FactoryInterface<Parser>
 */
final readonly class ParserFactory implements FactoryInterface
{
    /** @throws Throwable */
    #[Override]
    public function __invoke(ContainerInterface $container): Parser
    {
        $parserFactory = $container->get(\PhpParser\ParserFactory::class);

        $phpVersion = $container->get(PhpVersion::class);

        return $parserFactory->createForVersion($phpVersion);
    }
}
