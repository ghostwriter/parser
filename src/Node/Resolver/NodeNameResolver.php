<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Node\Resolver;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\UseItem;
use RuntimeException;

use function dd;

final readonly class NodeNameResolver
{


    private function resolveClassName(ClassLike $classLike): string
    {
        $namespacedName = $classLike->namespacedName;
        if ($namespacedName instanceof Name) {
            return $namespacedName->toString();
        }

        $name = $classLike->name;
        if ($name instanceof Identifier) {
            return $name->toString();
        }

        return '';
    }
    private function resolveFunctionName(Name $name): string
    {
        $functionName = $name->toString();

//        if (! $name instanceof FullyQualified) {
//            return $functionName;
//        }

        if (! $name instanceof FullyQualified) {
            return $functionName;
        }

        $namespacedName = $name->namespacedName;
        if (! $namespacedName instanceof Name) {
            return $functionName;
        }
        return $namespacedName->toString();

        return match (true) {
            in_array($namespacedNameString, $this->fileFunctions, true) => $namespacedNameString,
            default => $functionName,
        };
    }
    public function namespacedName(Node $node): ?string
    {
        assert(
            $node instanceof Name
            || $node instanceof ClassLike
            || $node instanceof Function_
            || $node instanceof Class_
            || $node instanceof Enum_
            || $node instanceof Interface_
            || $node instanceof Trait_
        );

        $name             = $node->name->toString();
        $namespacedName   = $node->namespacedName->toString();

        if (! $node->hasAttribute('namespacedName')) {
            return null;
        }

        $namespacedName = $node->getAttribute('namespacedName');

        if (! $namespacedName instanceof Name) {
            return null;
        }

        return $namespacedName->toString();
    }
    public function resolvedName(Node $node): string
    {
        assert(
            $node instanceof Name
            || $node instanceof Class_
            || $node instanceof Trait_
            || $node instanceof Interface_
            || $node instanceof Enum_
        );

        $name             = $node->name->toString();
        $namespacedName   = $node->namespacedName->toString();

//        $node instanceof Node\Stmt\Class_ &&
//        $node->namespacedName->toString()
//        $node = $node->namespacedName ?? $node->name;
//        assert($node instanceof Name);
        //replaceNodes (default true): Resolved names are replaced in-place.
        // Otherwise, a resolvedName attribute is added.
        // (Names that cannot be statically resolved receive a namespacedName attribute, as usual.)
        return match (true) {
            $node->hasAttribute('resolvedName') => $node->getAttribute('resolvedName')->toString(),
            default => $this->getName($node),
        };
    }

    private function namespace(string $namespacedName, string $name): string
    {
        return mb_trim(mb_rtrim($namespacedName, $name), '\\');
    }
    public function originalName1(Node $node): string
    {
        // Check if actual resolution occurred by comparing original to resolved
        // NameResolver preserves the original Name node in the 'originalName' attribute
        $originalName = $node->getAttribute('originalName');
        if ($originalName instanceof Name) {
            $originalNameString = $originalName->toString();
            $resolvedNameString = $this->resolve($node);
            if ($originalNameString !== $resolvedNameString) {
                return $originalNameString;
            }
            return $originalName->toString();
        }

        return $this->getName($node);
    }
    public function originalName(Node $node): string
    {
        if (! $node instanceof Name) {
            return $this->getName($node);
        }
        // preserveOriginalNames (default false): An "originalName" attribute will be added to all name nodes that underwent resolution.
        //replaceNodes (default true): Resolved names are replaced in-place. Otherwise, a resolvedName attribute is added. (Names that cannot be statically resolved receive a namespacedName attribute, as usual.)
        return match (true) {
            $node instanceof Name && $node->hasAttribute('originalName') => $node->getAttribute('originalName')->toString(),
            default => $this->getName($node),
        };
    }
    }

    public function resolve(Node $node): string
    {
        return match (true) {
            $node instanceof Name => $node->toString(),
            $node instanceof Identifier => $node->toString(),
            default => throw new RuntimeException('Unable to resolve name for node of type ' . $node::class),
        };
    }


public function getName(Node $node): string
{
    return match (true) {
        $node instanceof ClassConst => $node->consts[0]->name->toString(),
        $node instanceof ClassMethod => $node->name->toString(),
        $node instanceof Class_, $node instanceof Trait_, $node instanceof Interface_, $node instanceof Enum_ => $node->name?->toString(),
        $node instanceof EnumCase => $node->name->toString(),
        $node instanceof FunctionLike => $this->printer->print([$node]),
        $node instanceof Function_ => $node->name->toString(),
        $node instanceof Name => $node->toString(),
        $node instanceof Namespace_ => $node->name?->toString() ?? '',
        $node instanceof Property => $node->props[0]->name->toString(),
        $node instanceof TraitUse => $node->traits[0]->toString(),
        $node instanceof UseItem => $node->name->toString(),
        default => dd([__LINE__, __FUNCTION__, $node::class]),
    };
    /**
     * Returns locale independent base name of the given path.
     */
    protected function getNameString(string $name): string
    {
        $originalName = str_replace('\\', '/', $name);
        $pos = strrpos($originalName, '/');

        return false === $pos ? $originalName : substr($originalName, $pos + 1);
    }
}
