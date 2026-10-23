<?php

declare(strict_types=1);

namespace Irodev\TurkishText;

/** What a text is built on: its size, its most used terms and phrases, and how long its sentences are. */
final class KeywordReport
{
    /**
     * @param list<Keyword> $keywords most used first
     * @param list<Phrase>  $phrases  most used first
     */
    public function __construct(
        public readonly int $words,
        public readonly int $uniqueWords,
        public readonly int $averageSentenceLength,
        public readonly array $keywords,
        public readonly array $phrases,
    ) {
    }

    /** 'easy' (16 words a sentence or fewer), 'medium' (up to 24), 'hard', or 'unknown' when there are no full sentences. */
    public function sentenceLength(): string
    {
        $n = $this->averageSentenceLength;
        return $n === 0 ? 'unknown' : ($n <= 16 ? 'easy' : ($n <= 24 ? 'medium' : 'hard'));
    }
}
