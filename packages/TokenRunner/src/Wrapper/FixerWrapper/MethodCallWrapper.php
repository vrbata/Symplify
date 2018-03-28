<?php declare(strict_types=1);

namespace Symplify\TokenRunner\Wrapper\FixerWrapper;

use Nette\Utils\Strings;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Wrapper around "$this->_someCall_()" token
 */
final class MethodCallWrapper
{
    /**
     * @var Tokens
     */
    private $tokens;

    /**
     * @var int
     */
    private $index;

    /**
     * @var int
     */
    private $argumentsBracketStart;

    /**
     * @var int
     */
    private $argumentsBracketEnd;

    public function __construct(Tokens $tokens, int $index)
    {
        $this->tokens = $tokens;
        $this->index = $index;

        $this->argumentsBracketStart = $this->tokens->getNextTokenOfKind($this->index, ['(']);
        $this->argumentsBracketEnd = $this->tokens->findBlockEnd(
            Tokens::BLOCK_TYPE_PARENTHESIS_BRACE,
            $this->argumentsBracketStart
        );
    }

    public function getFirstLineLength(): int
    {
        $lineLength = 0;

        // compute from here to start of line
        $currentPosition = $this->index;
        while (! Strings::startsWith($this->tokens[$currentPosition]->getContent(), PHP_EOL)) {
            $lineLength += strlen($this->tokens[$currentPosition]->getContent());
            --$currentPosition;
        }

        $currentToken = $this->tokens[$currentPosition];

        // includes indent in the beginning
        $lineLength += strlen($currentToken->getContent());

        // minus end of lines, do not count PHP_EOL as characters
        $endOfLineCount = substr_count($currentToken->getContent(), PHP_EOL);
        $lineLength -= $endOfLineCount;

        // compute from here to end of line
        $currentPosition = $this->index + 1;
        while (! Strings::startsWith($this->tokens[$currentPosition]->getContent(), PHP_EOL)) {
            $lineLength += strlen($this->tokens[$currentPosition]->getContent());
            ++$currentPosition;
        }

        return $lineLength;
    }

    public function getArgumentsBracketStart(): int
    {
        return $this->argumentsBracketStart;
    }

    public function getArgumentsBracketEnd(): int
    {
        return $this->argumentsBracketEnd;
    }

    public function getLineLengthToEndOfArguments(): int
    {
        $lineLength = 0;

        // compute from function to start of line
        $currentPosition = $this->index;
        while (! Strings::startsWith($this->tokens[$currentPosition]->getContent(), PHP_EOL)) {
            $lineLength += strlen($this->tokens[$currentPosition]->getContent());
            --$currentPosition;
        }

        // get spaces to first line
        $lineLength += strlen($this->tokens[$currentPosition]->getContent());

        // get length from start of function till end of arguments - with spaces as one
        $currentPosition = $this->index;
        while ($currentPosition < $this->argumentsBracketEnd) {
            $currentToken = $this->tokens[$currentPosition];
            if ($currentToken->isGivenKind(T_WHITESPACE)) {
                ++$lineLength;
                ++$currentPosition;
                continue;
            }

            $lineLength += strlen($this->tokens[$currentPosition]->getContent());
            ++$currentPosition;
        }

        // get length from end or arguments to first line break
        $currentPosition = $this->argumentsBracketEnd;
        while (! Strings::startsWith($this->tokens[$currentPosition]->getContent(), PHP_EOL)) {
            $currentToken = $this->tokens[$currentPosition];

            $lineLength += strlen($currentToken->getContent());
            ++$currentPosition;
        }

        return $lineLength;
    }
}