<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Node\Traverser;

use Closure;
use Ghostwriter\Parser\Node\Visitor\CallableNodeVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;

final readonly class CallableNodeTraverser extends AbstractNodeTraverser
{
    /** @param Closure(Node):null|int|Node|Node[] $closure */
    public static function new(Closure $closure): self
    {
        return new self(new NodeTraverser(new CallableNodeVisitor($closure)));
    }
}
