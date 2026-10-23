<?php

declare(strict_types=1);

namespace Irodev\TurkishText;

/**
 * Keyword and phrase frequency of a text. In Turkish and English, word forms are counted together under their stem
 * (botlar, botları, bot; bots, bot) and shown as the form the text uses most. For each keyword it also tells whether
 * the page's title, H1 and meta description use it, in any form.
 */
final class Keywords
{
    /**
     * @param string $lang  'tr', 'en', or another language code (other languages compare plain words)
     * @param int    $limit how many keywords to return at most (a keyword needs two uses)
     */
    public static function analyze(string $text, string $lang = 'tr', string $title = '', string $h1 = '', string $description = '', int $limit = 12): KeywordReport
    {
        $tokens = Text::words($text, $lang);
        $total = count($tokens);
        $sw = Stopwords::all();
        $freq = [];
        $forms = [];
        foreach ($tokens as $t) {
            if (mb_strlen($t) < 3 || isset($sw[$t])) {
                continue;
            }
            $k = Stem::of($t, $lang);
            if (mb_strlen($k) < 3 || isset($sw[$k])) {                 // it's → it, kendini → kendi
                continue;
            }
            $freq[$k] = ($freq[$k] ?? 0) + 1;
            $forms[$k][$t] = ($forms[$k][$t] ?? 0) + 1;
        }
        arsort($freq);

        $places = [];
        foreach (['title' => $title, 'h1' => $h1, 'description' => $description] as $name => $s) {
            $places[$name] = [Text::lower($s, $lang), array_flip(Stem::terms($s, $lang))];
        }
        $in = static fn (string $place, string $word, string $key): bool => str_contains($places[$place][0], $word) || isset($places[$place][1][$key]);

        $keywords = [];
        foreach (array_slice($freq, 0, $limit, true) as $k => $n) {
            if ($n < 2) {
                break;
            }
            $k = (string) $k;
            $word = self::mostUsed($forms[$k]);
            $keywords[] = new Keyword($word, $n, $total ? round($n / $total * 100, 2) : 0.0,
                $in('title', $word, $k), $in('h1', $word, $k), $in('description', $word, $k));
        }

        $pairs = [];
        $pairForms = [];
        for ($i = 0; $i < $total - 1; $i++) {
            [$a, $b] = [$tokens[$i], $tokens[$i + 1]];
            if (mb_strlen($a) < 3 || mb_strlen($b) < 3 || isset($sw[$a]) || isset($sw[$b])) {
                continue;
            }
            $k = Stem::of($a, $lang) . ' ' . Stem::of($b, $lang);
            $pairs[$k] = ($pairs[$k] ?? 0) + 1;
            $pairForms[$k][$a . ' ' . $b] = ($pairForms[$k][$a . ' ' . $b] ?? 0) + 1;
        }
        arsort($pairs);
        $phrases = [];
        foreach (array_slice($pairs, 0, 10, true) as $k => $n) {
            if ($n < 2) {
                continue;
            }
            $phrases[] = new Phrase(self::mostUsed($pairForms[$k]), $n);
        }

        $sentences = array_filter(preg_split('/[.!?…]+\s/u', $text) ?: [], fn (string $s) => mb_strlen(trim($s)) > 20);
        $average = $sentences ? (int) round($total / count($sentences)) : 0;
        return new KeywordReport($total, count(array_unique($tokens)), $average, $keywords, $phrases);
    }

    /**
     * The form a text uses most; on a tie the shorter one, which is closer to the dictionary form (içerik before
     * içeriklerdir).
     *
     * @param array<string, int> $forms form => uses
     */
    private static function mostUsed(array $forms): string
    {
        uksort($forms, fn ($a, $b) => [$forms[$b], mb_strlen((string) $a)] <=> [$forms[$a], mb_strlen((string) $b)]);
        return (string) array_key_first($forms);
    }
}
