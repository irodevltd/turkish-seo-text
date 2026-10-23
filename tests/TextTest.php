<?php

declare(strict_types=1);

namespace Irodev\TurkishText\Tests;

use Irodev\TurkishText\Stopwords;
use Irodev\TurkishText\Text;
use PHPUnit\Framework\TestCase;

final class TextTest extends TestCase
{
    public function testLowerKeepsTheTwoTurkishIs(): void
    {
        self::assertSame('istanbul ışığı', Text::lower('İSTANBUL IŞIĞI'));
        self::assertSame('ıstanbul', Text::lower('ISTANBUL'));
        self::assertSame('istanbul', Text::lower('ISTANBUL', 'en'));
        self::assertSame('istanbul', Text::lower('İstanbul', 'en'));
    }

    public function testWordsKeepApostrophesAndHyphens(): void
    {
        self::assertSame(["google'da", 'e-ticaret', 'sitesi'], Text::words("Google'da 2026 e-ticaret sitesi!"));
    }

    public function testTermsDropShortWordsAndStopWords(): void
    {
        self::assertSame(['kurumsal', 'web', 'sitesi', 'seo'], Text::terms('Kurumsal bir web sitesi ve SEO için'));
    }

    public function testDetectsTurkishText(): void
    {
        self::assertTrue(Text::looksTurkish('Bu sayfa işletmeniz için hızlı ve güvenli bir çözüm sunar.'));
        self::assertFalse(Text::looksTurkish('This page offers a fast and secure solution for your business.'));
        self::assertFalse(Text::looksTurkish('Kebab and baklava'));
    }

    public function testStopWords(): void
    {
        self::assertTrue(Stopwords::contains('için'));
        self::assertTrue(Stopwords::contains('the'));
        self::assertFalse(Stopwords::contains('yazılım'));
    }
}
