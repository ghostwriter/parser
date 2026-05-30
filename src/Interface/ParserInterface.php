<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Interface;

use PhpParser\Node;

interface ParserInterface
{
    /**
     *
     * @throws ParserExceptionInterface
     *
     * @return list<Node>
     */
    public function parse(string $code): array;

    /**
     *
     * @throws ParserExceptionInterface
     *
     * @return list<Node>
     */
    public function parseFile(string $path): array;
}
