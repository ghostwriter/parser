<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Node;

use Override;
use PhpParser\Node;
use PhpParser\NodeAbstract;

use function mb_strrchr;
use function mb_strrpos;
use function mb_substr;

abstract class AbstractNode extends NodeAbstract implements Node
{
    /** @var array<string, string> */
    public array $actions = [
        'remove' => 'Skeleton\Service\PhpParser\Node\Action\RemoveAction',
        'replace' => 'Skeleton\Service\PhpParser\Node\Action\ReplaceAction',
        'update' => 'Skeleton\Service\PhpParser\Node\Action\UpdateAction',
        'add' => 'Skeleton\Service\PhpParser\Node\Action\AddAction',
        'addBefore' => 'Skeleton\Service\PhpParser\Node\Action\AddBeforeAction',
        'addAfter' => 'Skeleton\Service\PhpParser\Node\Action\AddAfterAction',
        'addFirst' => 'Skeleton\Service\PhpParser\Node\Action\AddFirstAction',
        'addLast' => 'Skeleton\Service\PhpParser\Node\Action\AddLastAction',
        'addFirstIfNotExists' => 'Skeleton\Service\PhpParser\Node\Action\AddFirstIfNotExistsAction',
        'addLastIfNotExists' => 'Skeleton\Service\PhpParser\Node\Action\AddLastIfNotExistsAction',
        'addIfNotExists' => 'Skeleton\Service\PhpParser\Node\Action\AddIfNotExistsAction',
        'addBeforeIfNotExists' => 'Skeleton\Service\PhpParser\Node\Action\AddBeforeIfNotExistsAction',
        'addAfterIfNotExists' => 'Skeleton\Service\PhpParser\Node\Action\AddAfterIfNotExistsAction',
    ];

    public function __construct(
        public array $stmts = [],
        array $attributes = []
    ) {
        parent::__construct($attributes);
    }

    /** @return array{'stmts'} */
    #[Override]
    public function getSubNodeNames(): array
    {
        return ['stmts'];
    }

    #[Override]
    public function getType(): string
    {
        //        $lastOccurrence = mb_strrpos(self::class, '\\');

        //        return match ($lastOccurrence) {
        //            false => self::class,
        //            default => mb_substr(self::class, $lastOccurrence + 1),
        //        };

        return mb_strrchr(self::class, '\\') ?: self::class;
    }
}
