<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Node\Visitor;

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

abstract class AbstractNodeVisitor extends NodeVisitorAbstract implements NodeVisitor
{
    /**
     * If NodeVisitor::enterNode() returns DONT_TRAVERSE_CHILDREN, child nodes
     * of the current node will not be traversed for any visitors.
     *
     * For subsequent visitors enterNode() will still be called on the current
     * node and leaveNode() will also be invoked for the current node.
     */
    public const int DONT_TRAVERSE_CHILDREN = NodeVisitor::DONT_TRAVERSE_CHILDREN;

    /**
     * If NodeVisitor::enterNode() returns DONT_TRAVERSE_CURRENT_AND_CHILDREN, child nodes
     * of the current node will not be traversed for any visitors.
     *
     * For subsequent visitors enterNode() will not be called as well.
     * leaveNode() will be invoked for visitors that has enterNode() method invoked.
     */
    public const int DONT_TRAVERSE_CURRENT_AND_CHILDREN = NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;

    public const null NEXT_NODE = null;

    /**
     * If NodeVisitor::leaveNode() returns REMOVE_NODE for a node that occurs
     * in an array, it will be removed from the array.
     *
     * For subsequent visitors leaveNode() will still be invoked for the
     * removed node.
     */
    public const int REMOVE_NODE = NodeVisitor::REMOVE_NODE;

    /**
     * If NodeVisitor::enterNode() or NodeVisitor::leaveNode() returns REPLACE_WITH_NULL,
     * the node will be replaced with null. This is not a legal return value if the node is part
     * of an array, rather than another node.
     */
    public const int REPLACE_WITH_NULL = NodeVisitor::REPLACE_WITH_NULL;

    public const null SKIP_NODE = null;

    /**
     * If NodeVisitor::enterNode() or NodeVisitor::leaveNode() returns
     * STOP_TRAVERSAL, traversal is aborted.
     *
     * The afterTraverse() method will still be invoked.
     */
    public const int STOP_TRAVERSAL = NodeVisitor::STOP_TRAVERSAL;

    //    public function __construct(
    //        protected readonly Printer $printer,
    //        protected readonly NodeSorter $nodeSorter,
    //        protected readonly NodeComparer $nodeComparer,
    //    ) {}

    /** @var array<int,null|int|Node|Node[]> */
    private array $enterNodes = [];

    /** @var array<int,null|int|Node|Node[]> */
    private array $leaveNodes = [];

    /** @return null|Node[] */
    #[Override]
    public function afterTraverse(array $nodes): ?array
    {
        return null;
    }

    /** @return null|Node[] */
    #[Override]
    public function beforeTraverse(array $nodes): ?array
    {
        return null;
    }

    public function dontTraverseChildren(Node $node): int
    {
        return $this->enterNodes[spl_object_id($node)] = NodeVisitor::DONT_TRAVERSE_CHILDREN;
    }

    public function dontTraverseCurrentAndChildren(Node $node): int
    {
        return $this->enterNodes[spl_object_id($node)] = NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
    }

    // /**
    //  * @param Closure(Node $node): (null|int|Node|Node[]) $closure
    //  */
    // public function __construct(
    //     private readonly \Closure $closure
    // ) {}

    /** @return null|int|Node|Node[] */
    #[Override]
    public function enterNode(Node $node): null|array|int|Node
    {
        if ($this->skip($node)) {
            return self::SKIP_NODE;
        }

        $newNode = $this->format($node);

        return $this->replace($node, $newNode);
    }

    /** @return null|int|Node|Node[] */
    #[Override]
    public function leaveNode(Node $node): null|array|int|Node
    {
        $nodeId = spl_object_id($node);

        if (! array_key_exists($nodeId, $this->leaveNodes)) {
            if (! array_key_exists($nodeId, $this->enterNodes)) {
                return self::NEXT_NODE;
            }

            $newNode = $this->enterNodes[$nodeId];

            unset($this->enterNodes[$nodeId]);

            return $newNode;
        }

        $newNode = $this->leaveNodes[$nodeId];

        unset($this->leaveNodes[$nodeId]);

        return $newNode;
    }

    public function remove(Node $node): Node
    {
        $this->leaveNodes[spl_object_id($node)] = NodeVisitor::REMOVE_NODE;

        return $node;
    }

    public function removeNode(Node $node): int
    {
        return $this->leaveNodes[spl_object_id($node)] = NodeVisitor::REMOVE_NODE;
    }

    /**
     * @param Node                 $oldNode
     * @param null|int|Node|Node[] $newNode
     */
    public function replace(Node $oldNode, null|array|int|Node $newNode): Node
    {
        if (NodeVisitor::REMOVE_NODE === $newNode) {
            return $this->remove($oldNode);
        }

        $nodeId = spl_object_id($oldNode);

        $newNode = $this->enterNodes[$nodeId] = match (true) {
            $newNode instanceof Expr => match (true) {
                $oldNode instanceof Stmt => new Expression($newNode),
                default => $newNode,
            },
            default => $newNode,
        };

        return match (true) {
            is_array($newNode) => $oldNode,
            default => $newNode,
        };
    }

    /**
     * @param Node $oldNode
     * @param Node $newNode
     */
    public function replaceNode(Node $oldNode, Node $newNode): Node
    {
        $this->enterNodes[spl_object_id($oldNode)] = match (true) {
            $newNode instanceof Expr => match (true) {
                $oldNode instanceof Stmt => new Expression($newNode),
                default => $newNode,
            },
            default => $newNode,
        };

        return $newNode;
    }

    public function replaceWithNull(Node $node): int
    {
        return $this->enterNodes[spl_object_id($node)] = NodeVisitor::REPLACE_WITH_NULL;
    }

    public function stopTraversal(Node $node): int
    {
        return $this->enterNodes[spl_object_id($node)] = NodeVisitor::STOP_TRAVERSAL;
    }

    //    /** @return null|int|Node|Node[] */
    //    abstract public function format(Node $node): null|array|int|Node;
    //
    //    public function skip(Node $node): bool
    //    {
    //        return false;
    //    }
}
