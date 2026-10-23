<?php

declare(strict_types=1);

namespace Irodev\TurkishText;

/**
 * A light Turkish stemmer for matching word forms, not for linguistics: stüdyo = stüdyosu, bot = botları,
 * görünürlük = görünürlüğü, proje = projenizi, Google = Google'da, zekâ = zeka, kitap = kitabı.
 *
 * It peels inflectional suffixes (plural, possessive, case, -ki, the -dır copula) off the end of a lower-case word
 * while vowel harmony, the buffer letters (y, s, n after a vowel) and the d/t alternation allow, keeps a stem of at
 * least three letters, then undoes consonant softening (kitab → kitap). Derivational suffixes (-lık, -lı, -sız, -cı,
 * -ca) are left alone on purpose: they make a different word (yazı ≠ yazılım, görünür ≠ görünürlük).
 *
 * The result is a comparison key, not a dictionary form: show the user the original words. Known limit: a final y
 * is not folded, so "kolay" and "kolayı" stay apart (folding it would merge yapay and yapı).
 */
final class TurkishStemmer
{
    private const VOWELS = 'aeıioöuü';
    private const VOICELESS = 'çfhkpsşt';

    /** @var list<array{string, string}>|null [suffix, what the stem must end with: 'v' vowel, 'c' consonant, '' either], longest first */
    private static ?array $suffixes = null;

    /** @var array<string, string> */
    private static array $memo = [];

    public static function stem(string $word): string
    {
        if (isset(self::$memo[$word])) {
            return self::$memo[$word];
        }
        $s = strtr((string) preg_replace('/[\'’].*$/u', '', $word), ['â' => 'a', 'î' => 'i', 'û' => 'u']);   // google'da → google
        for ($pass = 0; $pass < 6; $pass++) {
            $cut = false;
            foreach (self::suffixes() as [$suffix, $after]) {
                if (!str_ends_with($s, $suffix)) {
                    continue;
                }
                $stem = substr($s, 0, -strlen($suffix));
                if (mb_strlen($stem) < 3 || !preg_match('/[aeıioöuü]/u', $stem)) {
                    continue;
                }
                $last = mb_substr($stem, -1);
                $endsInVowel = str_contains(self::VOWELS, $last);
                if (($after === 'v' && !$endsInVowel) || ($after === 'c' && $endsInVowel)) {
                    continue;
                }
                $first = mb_substr($suffix, 0, 1);
                if (($first === 't' && !str_contains(self::VOICELESS, $last)) || ($first === 'd' && str_contains(self::VOICELESS, $last))) {
                    continue;
                }
                if (!self::harmonises($stem, $suffix)) {
                    continue;
                }
                $s = $stem;
                $cut = true;
                break;
            }
            // n and s after a vowel are buffer letters or part of the word (telefon+u reads as telefo+nu, servis+i as
            // servi+si): dropping them from every key, then peeling on, makes both readings meet. Not y: yapay ≠ yapı.
            if (!$cut && mb_strlen($s) >= 4 && preg_match('/[aeıioöuü][ns]$/u', $s)) {
                $s = mb_substr($s, 0, -1);
                $cut = true;
            }
            if (!$cut) {
                break;
            }
        }
        if (mb_strlen($s) >= 3) {                                     // kitab → kitap, çocuğ → çocuk, reng → renk
            $s = (string) preg_replace(['/b$/u', '/c$/u', '/d$/u', '/ğ$/u', '/ng$/u'], ['p', 'ç', 't', 'k', 'nk'], $s);
        }
        if (count(self::$memo) > 20000) {
            self::$memo = [];
        }
        return self::$memo[$word] = $s;
    }

    /** Does the suffix's vowel follow the stem's last vowel (a/e two-way, ı/i/u/ü four-way harmony)? */
    private static function harmonises(string $stem, string $suffix): bool
    {
        if (!preg_match('/[aeıioöuü](?!.*[aeıioöuü])/u', $stem, $lv)) {
            return false;
        }
        if (!preg_match('/[aeıioöuü]/u', $suffix, $sv)) {
            return true;
        }
        $back = str_contains('aıou', $lv[0]);
        if ($sv[0] === 'a' || $sv[0] === 'e') {
            return $sv[0] === ($back ? 'a' : 'e');
        }
        return $sv[0] === ['a' => 'ı', 'ı' => 'ı', 'o' => 'u', 'u' => 'u', 'e' => 'i', 'i' => 'i', 'ö' => 'ü', 'ü' => 'ü'][$lv[0]];
    }

    /** @return list<array{string, string}> */
    private static function suffixes(): array
    {
        if (self::$suffixes === null) {
            $list = [
                ['lar', ''], ['ler', ''],
                ['ımız', 'c'], ['imiz', 'c'], ['umuz', 'c'], ['ümüz', 'c'], ['ınız', 'c'], ['iniz', 'c'], ['unuz', 'c'], ['ünüz', 'c'],
                ['mız', 'v'], ['miz', 'v'], ['muz', 'v'], ['müz', 'v'], ['nız', 'v'], ['niz', 'v'], ['nuz', 'v'], ['nüz', 'v'],
                ['sı', 'v'], ['si', 'v'], ['su', 'v'], ['sü', 'v'], ['yı', 'v'], ['yi', 'v'], ['yu', 'v'], ['yü', 'v'],
                ['nı', 'v'], ['ni', 'v'], ['nu', 'v'], ['nü', 'v'], ['nın', 'v'], ['nin', 'v'], ['nun', 'v'], ['nün', 'v'],
                ['ın', 'c'], ['in', 'c'], ['un', 'c'], ['ün', 'c'], ['ı', 'c'], ['i', 'c'], ['u', 'c'], ['ü', 'c'],
                ['ya', 'v'], ['ye', 'v'], ['na', 'v'], ['ne', 'v'], ['a', 'c'], ['e', 'c'],
                ['da', ''], ['de', ''], ['ta', ''], ['te', ''], ['nda', 'v'], ['nde', 'v'],
                ['dan', ''], ['den', ''], ['tan', ''], ['ten', ''], ['ndan', 'v'], ['nden', 'v'],
                ['daki', ''], ['deki', ''], ['taki', ''], ['teki', ''], ['ndaki', 'v'], ['ndeki', 'v'],
                ['yla', 'v'], ['yle', 'v'], ['la', 'c'], ['le', 'c'],
                ['dır', ''], ['dir', ''], ['dur', ''], ['dür', ''], ['tır', ''], ['tir', ''], ['tur', ''], ['tür', ''],
            ];
            usort($list, fn (array $a, array $b) => mb_strlen($b[0]) <=> mb_strlen($a[0]));
            self::$suffixes = $list;
        }
        return self::$suffixes;
    }
}
