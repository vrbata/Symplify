<?php declare(strict_types=1);

namespace Symplify\TokenRunner\Analyzer\FixerAnalyzer;

use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;

final class IndentDetector
{
    public function detectOnPosition(
        Tokens $tokens,
        int $startIndex,
        WhitespacesFixerConfig $whitespacesFixerConfig
    ): int {
        for ($i = $startIndex; $i > 0; --$i) {
            $token = $tokens[$i];

            if ($token->isWhitespace() && $token->getContent() !== ' ') {
                return substr_count($token->getContent(), $whitespacesFixerConfig->getIndent());
            }
        }

        return 0;
    }
}