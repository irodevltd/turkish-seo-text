<?php

declare(strict_types=1);

namespace Irodev\TurkishText;

/** Lower-casing, word splitting and language detection that get Turkish right. */
final class Text
{
    /**
     * Lower-case. In Turkish, dotted and dotless i stay apart (İ → i, I → ı), which mb_strtolower() alone gets wrong;
     * in other languages İ becomes a plain i.
     */
    public static function lower(string $s, string $lang = 'tr'): string
    {
        $s = $lang === 'tr' ? strtr($s, ['İ' => 'i', 'I' => 'ı']) : str_replace('İ', 'i', $s);
        return mb_strtolower($s, 'UTF-8');
    }

    /**
     * The words of a text, lower-cased, in order. Apostrophes and hyphens inside a word are kept (Google'da,
     * e-ticaret); numbers and punctuation are left out.
     *
     * @return list<string>
     */
    public static function words(string $s, string $lang = 'tr'): array
    {
        preg_match_all('/\p{L}[\p{L}\p{Mn}\'’-]*/u', self::lower($s, $lang), $m);
        return $m[0];
    }

    /**
     * The meaningful words of a short text (a title, a heading): at least three letters, no stop words, each once.
     *
     * @return list<string>
     */
    public static function terms(string $s, string $lang = 'tr'): array
    {
        $sw = Stopwords::all();
        return array_values(array_unique(array_filter(self::words($s, $lang), fn (string $t) => mb_strlen($t) >= 3 && !isset($sw[$t]))));
    }

    /**
     * Is this text Turkish? For pages whose lang attribute is missing or wrong (a Turkish site left on lang="en"):
     * Turkish-only letters (ğ, ş, ı) plus more Turkish than English function words.
     */
    public static function looksTurkish(string $s): bool
    {
        static $tr = null, $en = null;
        $tr ??= array_flip(['ve', 'bir', 'için', 'ile', 'bu', 'çok', 'daha', 'olan', 'gibi', 'veya', 'ama', 'kadar', 'sonra', 'nasıl', 'değil', 'olarak', 'sizin', 'bizim']);
        $en ??= array_flip(['the', 'and', 'for', 'with', 'that', 'this', 'from', 'your', 'you', 'are', 'our', 'what', 'how', 'can']);
        $low = self::lower($s, 'tr');
        $letters = preg_match_all('/[ğşı]/u', $low);
        preg_match_all('/\p{L}+/u', $low, $m);
        $t = 0;
        $e = 0;
        foreach ($m[0] as $w) {
            if (isset($tr[$w])) {
                $t++;
            } elseif (isset($en[$w])) {
                $e++;
            }
        }
        return $letters >= 2 && ($t > $e || ($e === 0 && $letters >= 3));
    }
}
