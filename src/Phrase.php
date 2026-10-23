<?php

declare(strict_types=1);

namespace Irodev\TurkishText;

/** A two-word phrase the text repeats, shown in the form it uses most. */
final class Phrase
{
    public function __construct(
        public readonly string $text,
        public readonly int $count,
    ) {
    }
}
