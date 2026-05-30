<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Node\Traverser;

use Override;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;

abstract readonly class AbstractNodeTraverser implements NodeTraverserInterface
{
    public function __construct(
        public NodeTraverser $nodeTraverser
    ) {}

    #[Override]
    public function addVisitor(NodeVisitor $visitor): void
    {
        $this->nodeTraverser->addVisitor($visitor);
    }

    #[Override]
    public function removeVisitor(NodeVisitor $visitor): void
    {
        $this->nodeTraverser->removeVisitor($visitor);
    }

    /**
     * @param Node[] $nodes
     *
     * @return Node[]
     */
    #[Override]
    public function traverse(array $nodes): array
    {
        return match (true) {
            empty($nodes) => $nodes,
            default => $this->nodeTraverser->traverse($nodes),
        };
    }
}
