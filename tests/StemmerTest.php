<?php

declare(strict_types=1);

namespace Irodev\TurkishText\Tests;

use Irodev\TurkishText\EnglishStemmer;
use Irodev\TurkishText\Stem;
use Irodev\TurkishText\TurkishStemmer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StemmerTest extends TestCase
{
    #[DataProvider('turkishSameWord')]
    public function testTurkishFormsOfOneWordMeet(string $a, string $b): void
    {
        self::assertSame(TurkishStemmer::stem($a), TurkishStemmer::stem($b), "$a / $b");
    }

    /** @return iterable<array{string, string}> */
    public static function turkishSameWord(): iterable
    {
        yield ['stüdyo', 'stüdyosu'];
        yield ['telefon', 'telefonu'];
        yield ['servis', 'servisi'];
        yield ['kitap', 'kitabı'];
        yield ['google', "google'da"];
        yield ['bot', 'botlar'];
        yield ['bot', 'botları'];
        yield ['görünürlük', 'görünürlüğü'];
        yield ['proje', 'projenizi'];
        yield ['zekâ', 'zeka'];
        yield ['çocuk', 'çocuğu'];
        yield ['site', 'sitesi'];
        yield ['site', 'sitelerimiz'];
        yield ['müşteri', 'müşterilerden'];
        yield ['analiz', 'analizde'];
        yield ['tasarım', 'tasarımı'];
        yield ['yazılım', 'yazılımlar'];
    }

    #[DataProvider('turkishDifferentWords')]
    public function testDerivedTurkishWordsStayApart(string $a, string $b): void
    {
        self::assertNotSame(TurkishStemmer::stem($a), TurkishStemmer::stem($b), "$a / $b");
    }

    /** @return iterable<array{string, string}> */
    public static function turkishDifferentWords(): iterable
    {
        yield ['bot', 'botanik'];
        yield ['yazı', 'yazılım'];
        yield ['görünür', 'görünürlük'];
        yield ['yapay', 'yapı'];
        yield ['göz', 'gözlük'];
        yield ['tuz', 'tuzsuz'];
    }

    #[DataProvider('englishSameWord')]
    public function testEnglishPluralsAndPossessivesMeet(string $a, string $b): void
    {
        self::assertSame(EnglishStemmer::stem($a), EnglishStemmer::stem($b), "$a / $b");
    }

    /** @return iterable<array{string, string}> */
    public static function englishSameWord(): iterable
    {
        yield ['class', 'classes'];
        yield ['agency', 'agencies'];
        yield ['cookie', 'cookies'];
        yield ['bot', 'bots'];
        yield ['service', 'services'];
        yield ['site', "site's"];
        yield ['box', 'boxes'];
    }

    public function testEnglishKeepsWordsThatOnlyLookPlural(): void
    {
        self::assertNotSame(EnglishStemmer::stem('new'), EnglishStemmer::stem('news'));
        self::assertNotSame(EnglishStemmer::stem('design'), EnglishStemmer::stem('designer'));
        self::assertSame('business', EnglishStemmer::stem('business'));
        self::assertSame('status', EnglishStemmer::stem('status'));
        self::assertSame('analysis', EnglishStemmer::stem('analysis'));
    }

    public function testStemPicksTheLanguage(): void
    {
        self::assertTrue(Stem::same('Stüdyo', 'STÜDYOSU'));
        self::assertTrue(Stem::same('Agencies', 'agency', 'en'));
        self::assertFalse(Stem::same('casa', 'casas', 'es'));
        self::assertSame(['sit', 'tasarım'], Stem::terms('Siteler ve tasarımı', 'tr'));   // keys, not dictionary forms
    }
}
