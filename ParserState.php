<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Yaml;

use Symfony\Component\Yaml\Exception\ParseException;

/**
 * @internal
 */
final class ParserState
{
    public $maxNestingLevel = Parser::DEFAULT_MAX_NESTING_LEVEL;
    public $currentNestingLevel = 0;

    public function reset(): void
    {
        $this->currentNestingLevel = 0;
    }

    public function enterNestingLevel(int $line, ?string $snippet, ?string $filename): void
    {
        if (++$this->currentNestingLevel > $this->maxNestingLevel) {
            --$this->currentNestingLevel;

            throw new ParseException(sprintf('Maximum nesting depth of %d exceeded.', $this->maxNestingLevel), $line, $snippet, $filename);
        }
    }

    public function leaveNestingLevel(): void
    {
        if ($this->currentNestingLevel > 0) {
            --$this->currentNestingLevel;
        }
    }
}
