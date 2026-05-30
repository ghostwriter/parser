<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Node\Visitor;

use Closure;
use Override;
use PhpParser\Node;

final class FirstNodeFinderVisitor extends AbstractNodeVisitor
{
    private ?Node $node = null;

    /** @param Closure(Node): bool $closure */
    public function __construct(
        private readonly Closure $closure
    ) {}

    #[Override]
    public function beforeTraverse(array $nodes): ?array
    {
        $this->node = null;

        return null;
    }

    #[Override]
    public function enterNode(Node $node): ?int
    {
        if (! ($this->closure)($node)) {
            return null;
        }

        $this->node = $node;

        return self::STOP_TRAVERSAL;
    }

    public function getNode(): ?Node
    {
        return $this->node;
    }
}
