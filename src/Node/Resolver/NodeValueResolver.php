<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Node\Resolver;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use RuntimeException;

final readonly class NodeValueResolver
{
    public function resolve(Node $node): mixed
    {
        if ($node instanceof Node\Scalar\Int_) {
            return $node->value;
        }

        if ($node instanceof Node\Scalar\Float_) {
            return $node->value;
        }

        if ($node instanceof String_) {
            return $node->value;
        }

        throw new RuntimeException('Unable to resolve value for node of type ' . $node::class);
    }
}
