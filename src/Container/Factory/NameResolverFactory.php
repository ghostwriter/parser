<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Container\Factory;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;
use Override;
use PhpParser\ErrorHandler;
use PhpParser\NodeVisitor\NameResolver;
use Throwable;

/**
 * @see NameResolverFactoryTest
 *
 * @implements FactoryInterface<NameResolver>
 */
final readonly class NameResolverFactory implements FactoryInterface
{
    /** @throws Throwable */
    #[Override]
    public function __invoke(ContainerInterface $container): NameResolver
    {
        // If the `preserveOriginalNames` option is enabled,
        // then the resolved (fully qualified) name will have an `originalName` attribute,
        // which contains the unresolved name.

        // If the `replaceNodes` option is disabled,
        // then names will no longer be resolved in-place.
        // Instead, a `resolvedName` attribute will be added to each name,
        // which contains the resolved (fully qualified) name.
        // Once again, if an unqualified function or constant name cannot be resolved,
        // then the `resolvedName` attribute will not be present,
        // and instead a `namespacedName` attribute is added.
        //
        // The `replaceNodes` attribute is useful if you wish to perform modifications on the AST,
        // as you probably do not wish the resulting code to have fully resolved names as a side-effect.
        return new NameResolver($container->get(ErrorHandler::class), [
            'preserveOriginalNames' => true,
            'replaceNodes' => false,
        ]);
    }
}
