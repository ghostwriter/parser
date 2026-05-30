<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Node\Visitor;

use Closure;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

use function array_key_exists;
use function is_array;
use function spl_object_id;

final class CallableNodeVisitor extends NodeVisitorAbstract
{
    /** @var array<int,null|int|Node|Node[]> */
    private array $nodes = [];

    /** @param Closure(Node $closure): (null|int|Node|Node[]) $closure */
    public function __construct(
        private readonly Closure $closure
    ) {}

    /** @return null|int|Node|Node[] */
    #[Override]
    public function enterNode(Node $node): null|array|int|Node
    {
        $nodeId = spl_object_id($node);

        $newNode = ($this->closure)($node);

        $newNode = $this->nodes[$nodeId] = match (true) {
            $newNode instanceof Expr => match (true) {
                $node instanceof Stmt => new Expression($newNode),
                default => $newNode,
            },
            default => $newNode,
        };

        return match (true) {
            NodeVisitor::REMOVE_NODE === $newNode,
            is_array($newNode) => $node,
            default => $newNode,
        };
    }

    /** @return null|int|Node|Node[] */
    #[Override]
    public function leaveNode(Node $node): null|array|int|Node
    {
        $nodeId = spl_object_id($node);

        if (! array_key_exists($nodeId, $this->nodes)) {
            return null;
        }

        $newNode = $this->nodes[$nodeId];

        unset($this->nodes[$nodeId]);

        return $newNode;
    }
}
