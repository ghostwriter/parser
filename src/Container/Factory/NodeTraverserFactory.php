<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Container\Factory;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;
use Override;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitorAbstract;
use Throwable;

use function spl_object_hash;

/**
 * @see NodeTraverserFactoryTest
 *
 * @implements FactoryInterface<NodeTraverser>
 */
final readonly class NodeTraverserFactory implements FactoryInterface
{
    /** @throws Throwable */
    #[Override]
    public function __invoke(ContainerInterface $container): NodeTraverser
    {
        return new NodeTraverser(
            new class() extends NodeVisitorAbstract {
                public function enterNode(Node $node): Node
                {
                    $node->setAttribute('origNode', clone $node);
                    $node->setAttribute(self::class, spl_object_hash($node));

                    return $node;
                }
            },
            $container->get(NameResolver::class)
        );
    }
}
