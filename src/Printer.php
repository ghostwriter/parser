<?php

declare(strict_types=1);

namespace Ghostwriter\Parser;

use Override;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\PrettyPrinter\Standard;

use function sprintf;

final class Printer extends Standard
{
    #[Override]
    protected function pStmt_Declare(Declare_ $node): string
    {
        $commaSeparated = $this->pCommaSeparated($node->declares);

        return sprintf(
            'declare(%s)%s',
            $commaSeparated,
            match (true) {
                null === $node->stmts => ';',
                default => ' {' . $this->pStmts($node->stmts) . $this->nl . '}',
            }
        );
    }

    //    protected function pUseType(int $type): string {
    //        return $type === Stmt\Use_::TYPE_FUNCTION ? 'function '
    //            : ($type === Stmt\Use_::TYPE_CONSTANT ? 'const ' : '');
    //    }
}
