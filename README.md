# Turkish SEO text tools for PHP

[![tests](https://github.com/irodevltd/turkish-seo-text/actions/workflows/tests.yml/badge.svg)](https://github.com/irodevltd/turkish-seo-text/actions/workflows/tests.yml)
![PHP](https://img.shields.io/badge/php-%3E%3D8.1-777bb4)
![License](https://img.shields.io/badge/license-MIT-blue)

**English** · [Türkçe](README.tr.md)

Turkish is agglutinative: *site, sitesi, siteler, sitelerimizden* are one word to a reader, but four words to a naive
keyword counter. This library gives PHP the pieces an SEO tool needs to read Turkish properly, with English support
for mixed pages:

- **`TurkishStemmer`**: a light stemmer that follows vowel harmony, buffer letters (y, s, n) and consonant softening,
  so *kitap = kitabı*, *proje = projenizi*, *Google = Google'da*. It keeps derived words apart: *yazı ≠ yazılım*.
- **`Readability`**: the Ateşman (1997) formula for Turkish and Flesch Reading Ease for English, with their bands.
- **`Keywords`**: keyword density and repeated phrases with word forms counted together, plus whether the title, H1 and
  meta description use each keyword.
- **`Text`**: lower-casing that keeps *İ/i* and *I/ı* apart, word splitting, and Turkish language detection.

These are the text routines behind the free [SEO audit at irodev.net](https://irodev.net/ucretsiz-seo-analizi), made
standalone. No dependencies beyond `mbstring`.

## Installation

```bash
composer require irodevnet/turkish-seo-text
```

## Usage

### Stems: match word forms

```php
use Irodev\TurkishText\Stem;
use Irodev\TurkishText\TurkishStemmer;

TurkishStemmer::stem('kitabı');            // "kitap"
TurkishStemmer::stem("google'da");         // "google"
Stem::same('Stüdyo', 'STÜDYOSU');          // true
Stem::same('yazı', 'yazılım');             // false: a derived word is a different word
Stem::same('agencies', 'agency', 'en');    // true
```

A stem is a comparison key, not a dictionary form (*site* gives *sit*). Show users the original words.

### Keywords: what a page is built on

```php
use Irodev\TurkishText\Keywords;

$report = Keywords::analyze($bodyText, 'tr', $title, $h1, $metaDescription);

foreach ($report->keywords as $k) {
    echo "$k->word ×$k->count ($k->density%)", $k->inTitle ? ' · in title' : '', PHP_EOL;
}
// botu ×8 (19.51%) · in title   ← bot, botlar, botları, botu, botların together, shown as the most used form

foreach ($report->phrases as $p) {
    echo "$p->text ×$p->count", PHP_EOL;          // telegram botları ×3
}

$report->averageSentenceLength;                   // words per sentence
$report->sentenceLength();                        // 'easy' (≤16), 'medium' (≤24), 'hard'
```

### Readability

```php
use Irodev\TurkishText\Readability;

$r = Readability::score($paragraphs, 'tr');       // Ateşman for 'tr', Flesch otherwise; null under 100 words
if ($r !== null) {
    echo "$r->score/100 · {$r->level()}";         // 52/100 · Orta güçlükte
}
```

| Ateşman | Level | | Flesch | Level |
| --- | --- | --- | --- | --- |
| 90–100 | Çok kolay | | 90–100 | Very easy |
| 70–89 | Kolay | | 80–89 | Easy |
| 50–69 | Orta güçlükte | | 70–79 | Fairly easy |
| 30–49 | Zor | | 60–69 | Standard |
| 0–29 | Çok zor | | 50–59 | Fairly difficult |
| | | | 30–49 | Difficult |
| | | | 0–29 | Very difficult |

Feed it the paragraph text of a page, not menus and footers: they have no sentences and skew the score.

### Text helpers

```php
use Irodev\TurkishText\Text;

Text::lower('İSTANBUL IŞIĞI');                    // "istanbul ışığı" (mb_strtolower gives "i̇stanbul işiği")
Text::words("Google'da e-ticaret sitesi");        // ["google'da", "e-ticaret", "sitesi"]
Text::looksTurkish($text);                        // for pages whose lang attribute is missing or wrong
```

## Known limits

The stemmer is built for matching keywords, and it is deliberately simple:

- It only removes inflectional suffixes (plural, possessive, case, -ki, -dır). Derivational suffixes such as -lık,
  -lı, -sız and -cı stay, because they make a different word.
- A final *y* is not folded, so *kolay* and *kolayı* stay apart (folding it would merge *yapay* and *yapı*).
- Treating *n* and *s* after a vowel as buffer letters merges a few unrelated words, for example *sorun* and *soru*.

The test suite lists the pairs that must meet and the pairs that must stay apart. If you find a common word that
goes wrong, an issue with the pair is very welcome.

## Testing

```bash
composer install
composer test
```

## Sources

- Ateşman, E. (1997). Türkçede okunabilirliğin ölçülmesi. *Dil Dergisi*, 58, 71–74.
- Flesch, R. (1948). A new readability yardstick. *Journal of Applied Psychology*, 32(3), 221–233.

## License

MIT © [İRODEV LTD](https://irodev.net). Built by İrodev, a web and software studio for businesses in Turkey and the
UK. Try the tools on a live site with the [free SEO audit](https://irodev.net/ucretsiz-seo-analizi).
