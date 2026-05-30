<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Node\Visitor;

use Override;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;

final class SequentialNodeVisitor extends AbstractNodeVisitor
{
    /** @var NodeVisitor[] */
    private readonly array $visitors;

    public function __construct(NodeVisitor ...$visitors)
    {
        $this->visitors = $visitors;
    }

    /**
     * @param Node[] $nodes
     *
     * @return null|Node[]
     */
    #[Override]
    public function beforeTraverse(array $nodes): ?array
    {
        foreach ($this->visitors as $visitor) {
            $nodes = (new NodeTraverser($visitor))->traverse($nodes);
        }

        return $nodes;
    }

    /** @return null|int|Node|Node[] */
    #[Override]
    public function enterNode(Node $node): null|array|int|Node
    {
        return self::STOP_TRAVERSAL;
    }
}
