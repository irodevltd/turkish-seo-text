<?php

declare(strict_types=1);

namespace Irodev\TurkishText;

/** A readability result on a 0–100 scale where higher is easier. */
final class ReadabilityScore
{
    public function __construct(
        public readonly int $score,
        /** 'atesman' or 'flesch' */
        public readonly string $formula,
        public readonly int $words,
        public readonly int $sentences,
        public readonly int $syllables,
    ) {
    }

    public function wordsPerSentence(): float
    {
        return round($this->words / max(1, $this->sentences), 1);
    }

    /**
     * The band the score falls in: Ateşman's own Turkish bands for Turkish text, Flesch's bands otherwise.
     * On the web, 50 and above reads comfortably.
     */
    public function level(): string
    {
        $s = $this->score;
        if ($this->formula === 'atesman') {
            return match (true) {
                $s >= 90 => 'Çok kolay',
                $s >= 70 => 'Kolay',
                $s >= 50 => 'Orta güçlükte',
                $s >= 30 => 'Zor',
                default => 'Çok zor',
            };
        }
        return match (true) {
            $s >= 90 => 'Very easy',
            $s >= 80 => 'Easy',
            $s >= 70 => 'Fairly easy',
            $s >= 60 => 'Standard',
            $s >= 50 => 'Fairly difficult',
            $s >= 30 => 'Difficult',
            default => 'Very difficult',
        };
    }
}
