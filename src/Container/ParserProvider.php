<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Container;

use Ghostwriter\Container\Interface\Service\ExtensionInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;
use Ghostwriter\Container\Service\Provider\AbstractProvider;
use Ghostwriter\Parser\Printer;
use PhpParser\ErrorHandler;
use PhpParser\ErrorHandler\Collecting;
use PhpParser\ErrorHandler\Throwing;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\PhpVersion;
use PhpParser\PrettyPrinter;
use PhpParser\PrettyPrinter\Standard;

/**
 * @see PhpParserProviderTest
 */
final class ParserProvider extends AbstractProvider
{
    /**
     * [alias => service].
     *
     * @var array<class-string,class-string>
     */
    public const array ALIAS = [
        ErrorHandler::class => Collecting::class,
        PrettyPrinter::class => Standard::class,
        Standard::class => Printer::class,
    ];

    /**
     * [concrete => [abstract => implementation]].
     *
     * @var array<class-string,array<class-string,class-string>>
     */
    public const array BIND = [];

    /**
     * [service => [extension, ...]].
     *
     * @var array<class-string,list<class-string<ExtensionInterface>>>
     */
    public const array EXTEND = [];

    /**
     * [service => factory].
     *
     * @var array<class-string,class-string<FactoryInterface>>
     */
    public const array FACTORY = [
        Printer::class => Factory\PrinterFactory::class,
        Collecting::class => Factory\CollectingErrorHandlerFactory::class,
        NameResolver::class => Factory\NameResolverFactory::class,
        NodeTraverser::class => Factory\NodeTraverserFactory::class,
        Parser::class => Factory\ParserFactory::class,
        PhpVersion::class => Factory\PhpVersionFactory::class,
        Throwing::class => Factory\ThrowingErrorHandlerFactory::class,
    ];
}
