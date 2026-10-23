<?php

declare(strict_types=1);

namespace Irodev\TurkishText;

/** One of a text's most used terms: its word forms counted together, shown as the form the text uses most. */
final class Keyword
{
    public function __construct(
        public readonly string $word,
        public readonly int $count,
        /** share of all words, in percent */
        public readonly float $density,
        public readonly bool $inTitle,
        public readonly bool $inH1,
        public readonly bool $inDescription,
    ) {
    }
}
