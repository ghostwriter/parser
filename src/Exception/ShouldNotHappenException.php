<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Exception;

use Ghostwriter\Parser\Interface\ParserExceptionInterface;
use LogicException;

final class ShouldNotHappenException extends LogicException implements ParserExceptionInterface {}
