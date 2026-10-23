<?php

declare(strict_types=1);

namespace Irodev\TurkishText\Tests;

use Irodev\TurkishText\Keywords;
use PHPUnit\Framework\TestCase;

final class KeywordsTest extends TestCase
{
    private const TEXT = 'Telegram botları işletmeler için zaman kazandırır. Bir bot siparişleri alır ve botlar müşterilere anında yanıt verir. '
        . 'Telegram botu kurmak kolaydır; botların bakımı da basittir. Sipariş botu ile randevu botu en çok istenen iki örnektir. '
        . 'İşletmeler Telegram botları sayesinde sipariş takibini otomatikleştirir.';

    public function testCountsWordFormsTogether(): void
    {
        $r = Keywords::analyze(self::TEXT, 'tr', 'Telegram Botları ile Sipariş', 'İşletmeler için Telegram botu', 'Telegram botuyla siparişleri otomatik alın.');

        self::assertSame(41, $r->words);
        $top = $r->keywords[0];
        self::assertSame('botu', $top->word);                 // bot, botlar, botları, botu, botların: shown as the most used form
        self::assertSame(8, $top->count);
        self::assertSame(19.51, $top->density);
        self::assertTrue($top->inTitle && $top->inH1 && $top->inDescription);
    }

    public function testTellsWhereEachKeywordAppears(): void
    {
        $r = Keywords::analyze(self::TEXT, 'tr', 'Telegram Botları ile Sipariş', 'İşletmeler için Telegram botu', 'Telegram botuyla siparişleri otomatik alın.');
        $byWord = [];
        foreach ($r->keywords as $k) {
            $byWord[$k->word] = [$k->count, $k->inTitle, $k->inH1, $k->inDescription];
        }

        self::assertSame([3, true, false, true], $byWord['sipariş']);
        self::assertSame([2, false, true, false], $byWord['işletmeler']);
    }

    public function testFindsRepeatedPhrases(): void
    {
        $r = Keywords::analyze(self::TEXT);

        self::assertSame('telegram botları', $r->phrases[0]->text);
        self::assertSame(3, $r->phrases[0]->count);
    }

    public function testMeasuresSentences(): void
    {
        $r = Keywords::analyze(self::TEXT);

        self::assertSame(8, $r->averageSentenceLength);
        self::assertSame('easy', $r->sentenceLength());
        self::assertSame('unknown', Keywords::analyze('')->sentenceLength());
    }

    public function testOtherLanguagesComparePlainWords(): void
    {
        $r = Keywords::analyze('Casa grande. Casas grandes. Casa bonita y casa nueva.', 'es');

        self::assertSame('casa', $r->keywords[0]->word);
        self::assertSame(3, $r->keywords[0]->count);
    }
}
