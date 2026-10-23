<?php

declare(strict_types=1);

namespace Irodev\TurkishText;

/** One word as a comparison key: the Turkish or English stem, or the word itself in other languages. */
final class Stem
{
    public static function of(string $word, string $lang = 'tr'): string
    {
        return match ($lang) {
            'tr' => TurkishStemmer::stem($word),
            'en' => EnglishStemmer::stem($word),
            default => $word,
        };
    }

    /**
     * The stems of a short text's meaningful words (Text::terms()), each once.
     *
     * @return list<string>
     */
    public static function terms(string $text, string $lang = 'tr'): array
    {
        return array_values(array_unique(array_map(fn (string $w) => self::of($w, $lang), Text::terms($text, $lang))));
    }

    /** Do two words share a stem? stüdyo / stüdyosu: yes; yazı / yazılım: no. */
    public static function same(string $a, string $b, string $lang = 'tr'): bool
    {
        return self::of(Text::lower($a, $lang), $lang) === self::of(Text::lower($b, $lang), $lang);
    }
}
