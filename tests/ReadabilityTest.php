<?php

declare(strict_types=1);

namespace Irodev\TurkishText\Tests;

use Irodev\TurkishText\Readability;
use PHPUnit\Framework\TestCase;

final class ReadabilityTest extends TestCase
{
    public function testAtesmanFromCountedSyllablesWordsAndSentences(): void
    {
        // 15 × ("Kedi süt içti." = 3 words, 5 syllables + a 4-word, 25-syllable sentence): 105 words, 30 sentences, 450 syllables
        $text = str_repeat('Kedi süt içti. Bilgisayarlarımızdaki uygulamaların güncellenmesi gerekmektedir. ', 15);
        $r = Readability::atesman($text);

        self::assertNotNull($r);
        self::assertSame([105, 30, 450], [$r->words, $r->sentences, $r->syllables]);
        self::assertSame((int) round(198.825 - 40.175 * (450 / 105) - 2.610 * (105 / 30)), $r->score);
        self::assertSame(18, $r->score);
        self::assertSame('atesman', $r->formula);
        self::assertSame('Çok zor', $r->level());
    }

    public function testFleschForEnglish(): void
    {
        $text = str_repeat('The cat sat on the mat. The dog ran to the park and played with a ball. International organizations regularly publish complicated documentation. ', 6);
        $r = Readability::score($text, 'en');

        self::assertNotNull($r);
        self::assertSame([138, 18, 246], [$r->words, $r->sentences, $r->syllables]);
        self::assertSame(48, $r->score);
        self::assertSame('flesch', $r->formula);
        self::assertSame('Difficult', $r->level());
        self::assertSame(7.7, $r->wordsPerSentence());
    }

    public function testShortTextsAreNotJudged(): void
    {
        self::assertNull(Readability::score(str_repeat('Kısa bir cümle. ', 20)));
        self::assertNotNull(Readability::score(str_repeat('Kısa bir cümle. ', 20), 'tr', 50));
        self::assertNull(Readability::score(''));
    }

    public function testScoresStayBetweenZeroAndHundred(): void
    {
        self::assertSame(100, Readability::atesman(str_repeat('Su ak. ', 60))?->score);
        self::assertSame(0, Readability::atesman(str_repeat('Bilgisayarlarımızdaki uygulamaların güncellenmesi gerekmektedir ', 30) . '.')?->score);
    }

    public function testCountsSyllables(): void
    {
        self::assertSame(9, Readability::syllablesTr('Bilgisayarlarımızdaki'));
        self::assertSame(1, Readability::syllablesTr('süt'));
        self::assertSame(5, Readability::syllablesEn('international'));
        self::assertSame(1, Readability::syllablesEn('played'));
        self::assertSame(1, Readability::syllablesEn('the'));
    }
}
