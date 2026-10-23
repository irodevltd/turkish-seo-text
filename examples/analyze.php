<?php

declare(strict_types=1);

// php examples/analyze.php
// Analyse a short Turkish text the way an SEO audit would: what it is built on, and how hard it is to read.

require __DIR__ . '/../vendor/autoload.php';

use Irodev\TurkishText\Keywords;
use Irodev\TurkishText\Readability;
use Irodev\TurkishText\Text;

$title = 'Kurumsal Web Tasarımı: Hızlı ve Mobil Uyumlu Siteler';
$h1 = 'Müşteri getiren kurumsal web siteleri';
$description = 'Kurumsal web sitenizi hızlı, mobil uyumlu ve aramalarda bulunur şekilde tasarlıyoruz.';
$text = <<<'TXT'
Kurumsal web sitesi, işletmenizin internetteki ilk izlenimidir. Ziyaretçiler bir siteye ortalama birkaç saniye
içinde karar verir; bu yüzden sayfaların hızlı açılması ve telefonda düzgün görünmesi gerekir. Kurumsal sitelerde en
sık gördüğümüz sorunlar yavaş açılan sayfalar, eksik başlıklar ve güncellenmeyen içeriklerdir. İyi bir web tasarımı
bu sorunları baştan çözer: sayfalar hızlı açılır, başlıklar ne aradığınızı söyler, içerik kolayca güncellenir.
Arama motorları da hızlı ve düzenli siteleri sever. Kurumsal web sitenizi tasarlarken önce hedef müşterinizin ne
aradığını belirleyin, sonra her sayfayı tek bir soruya yanıt verecek şekilde kurgulayın. Böylece hem ziyaretçiler hem
de arama motorları sitenizi kolayca anlar. Web tasarımında görsellerin boyutunu küçültmek, metinleri kısa
paragraflara bölmek ve her sayfaya açık bir başlık vermek hızlı kazanımlardır.
TXT;

$lang = Text::looksTurkish($text) ? 'tr' : 'en';
$pad = fn (string $s, int $width): string => $s . str_repeat(' ', max(0, $width - mb_strlen($s)));   // printf counts bytes
$report = Keywords::analyze($text, $lang, $title, $h1, $description);

printf("Words: %d, unique: %d, average sentence: %d words (%s)\n\n", $report->words, $report->uniqueWords,
    $report->averageSentenceLength, $report->sentenceLength());

echo "Keyword            Count  Density  Title  H1  Description\n";
foreach ($report->keywords as $k) {
    printf("%s %5d  %6.2f%%  %-5s  %-3s %s\n", $pad($k->word, 18), $k->count, $k->density,
        $k->inTitle ? 'yes' : '-', $k->inH1 ? 'yes' : '-', $k->inDescription ? 'yes' : '-');
}

echo "\nPhrases: ", implode(', ', array_map(fn ($p) => $p->text . ' ×' . $p->count, $report->phrases)), "\n";

$readability = Readability::score($text, $lang);
echo $readability === null
    ? "\nReadability: the text is too short to judge.\n"
    : sprintf("\nReadability (%s): %d/100, %s\n", $readability->formula, $readability->score, $readability->level());
