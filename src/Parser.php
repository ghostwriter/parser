<?php

declare(strict_types=1);

namespace Ghostwriter\Parser;

use Ghostwriter\Container\Container;
use Ghostwriter\Filesystem\Interface\FilesystemInterface;
use Ghostwriter\Parser\Exception\ShouldNotHappenException;
use Ghostwriter\Parser\Interface\ParserExceptionInterface;
use Ghostwriter\Parser\Interface\ParserInterface;
use Override;
use PhpParser\Node;
use Throwable;

use function sprintf;

/**
 * @see WipTest
 */
final readonly class Parser implements ParserInterface
{
    public function __construct(
        private \PhpParser\Parser $parser,
        private FilesystemInterface $filesystem,
    ) {}

    public static function new(): self
    {
        return Container::getInstance()->get(self::class);
    }

    /**
     *
     * @throws ParserExceptionInterface
     *
     * @return list<Node>
     */
    #[Override]
    public function parse(string $code): array
    {
        try {
            $stmts = $this->parser->parse($code);
        } catch (Throwable $throwable) {
            throw new ShouldNotHappenException('Failed to parse code.', previous: $throwable);
        }

        if (null === $stmts) {
            throw new ShouldNotHappenException('Failed to parse code.');
        }

        return $stmts;
    }

    /**
     *
     * @throws ParserExceptionInterface
     *
     * @return list<Node>
     */
    #[Override]
    public function parseFile(string $path): array
    {
        $code = $this->filesystem->read($path);

        try {
            $stmts = $this->parser->parse($code);
        } catch (Throwable $throwable) {
            throw new ShouldNotHappenException(sprintf('Failed to parse file: %s', $path), previous: $throwable);
        }

        if (null === $stmts) {
            throw new ShouldNotHappenException(sprintf('Failed to parse file: %s', $path));
        }

        return $stmts;
    }
}
