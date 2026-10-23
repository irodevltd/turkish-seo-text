<?php

declare(strict_types=1);

namespace Irodev\TurkishText;

/**
 * Readability of running text: Ateşman (1997) for Turkish, Flesch Reading Ease (1948) otherwise. Both give 0–100,
 * higher is easier.
 *
 *   Ateşman = 198.825 − 40.175 × (syllables / words) − 2.610 × (words / sentences)
 *   Flesch  = 206.835 − 1.015 × (words / sentences) − 84.6 × (syllables / words)
 *
 * Feed it paragraph text, not a whole page: menus and footers have no sentences and skew the score.
 */
final class Readability
{
    /** Ateşman for Turkish, Flesch for everything else. Null under $minWords words: too little text to judge. */
    public static function score(string $text, string $lang = 'tr', int $minWords = 100): ?ReadabilityScore
    {
        return $lang === 'tr' ? self::atesman($text, $minWords) : self::flesch($text, $minWords);
    }

    public static function atesman(string $text, int $minWords = 100): ?ReadabilityScore
    {
        return self::measure($text, true, $minWords);
    }

    public static function flesch(string $text, int $minWords = 100): ?ReadabilityScore
    {
        return self::measure($text, false, $minWords);
    }

    /** Turkish syllables: one per vowel (Turkish has no diphthongs). */
    public static function syllablesTr(string $word): int
    {
        return max(1, (int) preg_match_all('/[aeıioöuüâîû]/u', Text::lower($word, 'tr')));
    }

    /** English syllables by vowel groups, good enough for a readability band. */
    public static function syllablesEn(string $word): int
    {
        $w = strtolower((string) preg_replace('/[^a-z]/i', '', $word));
        if (strlen($w) <= 3) {
            return 1;
        }
        $w = (string) preg_replace(['/(?:[^laeiouy]es|ed|[^laeiouy]e)$/', '/^y/'], '', $w);
        return max(1, (int) preg_match_all('/[aeiouy]{1,2}/', $w));
    }

    private static function measure(string $text, bool $turkish, int $minWords): ?ReadabilityScore
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));
        preg_match_all('/\p{L}+/u', $text, $m);
        $words = $m[0];
        $nw = count($words);
        if ($nw === 0 || $nw < $minWords) {
            return null;
        }
        $ns = max(1, (int) preg_match_all('/[^.!?…]+[.!?…]+(\s|$)/u', $text));
        $syllables = 0;
        foreach ($words as $w) {
            $syllables += $turkish ? self::syllablesTr($w) : self::syllablesEn($w);
        }
        $score = $turkish
            ? 198.825 - 40.175 * ($syllables / $nw) - 2.610 * ($nw / $ns)
            : 206.835 - 1.015 * ($nw / $ns) - 84.6 * ($syllables / $nw);
        return new ReadabilityScore((int) round(max(0, min(100, $score))), $turkish ? 'atesman' : 'flesch', $nw, $ns, $syllables);
    }
}
