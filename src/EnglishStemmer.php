<?php

declare(strict_types=1);

namespace Irodev\TurkishText;

/**
 * English plurals and possessives under one key: bots = bot, services = service, site's = site, classes = class,
 * agencies = agency, cookies = cookie. Words ending in -ss, -us and -is (business, status, analysis) keep their s,
 * and a plural never turns into a stop word (news stays news). A comparison key ("case" and "cases" both give "cas"),
 * not a dictionary form.
 */
final class EnglishStemmer
{
    /** @var array<string, string> */
    private static array $memo = [];

    public static function stem(string $word): string
    {
        if (isset(self::$memo[$word])) {
            return self::$memo[$word];
        }
        $s = (string) preg_replace('/[\'’]s?$/u', '', $word);                          // site's → site, sites' → sites
        if (strlen($s) > 4 && str_ends_with($s, 'ies')) {
            $s = substr($s, 0, -3) . 'y';                                                    // agencies → agency
        } elseif (strlen($s) > 3 && str_ends_with($s, 's') && !preg_match('/(ss|us|is)$/', $s) && !Stopwords::contains(substr($s, 0, -1))) {
            $s = substr($s, 0, -1);
        }
        if (strlen($s) > 3 && preg_match('/(s|x|z|ch|sh)e$/', $s)) {
            $s = substr($s, 0, -1);                                                          // classe(s) → class, box(es) → box
        }
        if (strlen($s) > 3 && str_ends_with($s, 'ie')) {
            $s = substr($s, 0, -2) . 'y';                                                    // cookie meets cookies
        }
        if (count(self::$memo) > 20000) {
            self::$memo = [];
        }
        return self::$memo[$word] = $s;
    }
}
