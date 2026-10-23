# PHP için Türkçe SEO metin araçları

[![tests](https://github.com/irodevltd/turkish-seo-text/actions/workflows/tests.yml/badge.svg)](https://github.com/irodevltd/turkish-seo-text/actions/workflows/tests.yml)
![PHP](https://img.shields.io/badge/php-%3E%3D8.1-777bb4)
![License](https://img.shields.io/badge/license-MIT-blue)

[English](README.md) · **Türkçe**

Türkçe eklemeli bir dildir: *site, sitesi, siteler, sitelerimizden* okur için tek kelimedir; basit bir anahtar
kelime sayacı içinse dört ayrı kelime. Bu kütüphane, bir SEO aracının Türkçeyi doğru okuması için gereken parçaları
PHP'ye getirir. Karışık sayfalar için İngilizce desteği de vardır:

- **`TurkishStemmer`:** Ünlü uyumunu, kaynaştırma harflerini (y, s, n) ve ünsüz yumuşamasını izleyen hafif bir kök
  bulucu: *kitap = kitabı*, *proje = projenizi*, *Google = Google'da*. Yapım ekiyle türeyen kelimeleri ayrı tutar:
  *yazı ≠ yazılım*.
- **`Readability`:** Türkçe için Ateşman (1997), İngilizce için Flesch okunabilirlik formülü ve düzeyleri.
- **`Keywords`:** Kelime biçimlerini birlikte sayan anahtar kelime yoğunluğu ve tekrarlanan öbekler. Her anahtar
  kelimenin başlıkta, H1'de ve meta açıklamada geçip geçmediğini de söyler.
- **`Text`:** *İ/i* ve *I/ı* ayrımını koruyan küçük harfe çevirme, kelimelere ayırma ve Türkçe metin tespiti.

[irodev.net'teki ücretsiz SEO analizinin](https://irodev.net/tr/ucretsiz-seo-analizi) metin işleyen bölümlerinin
bağımsız hâlidir. `mbstring` dışında bağımlılığı yoktur.

## Kurulum

```bash
composer require irodevnet/turkish-seo-text
```

## Kullanım

### Kökler: kelime biçimlerini eşleştirin

```php
use Irodev\TurkishText\Stem;
use Irodev\TurkishText\TurkishStemmer;

TurkishStemmer::stem('kitabı');            // "kitap"
TurkishStemmer::stem("google'da");         // "google"
Stem::same('Stüdyo', 'STÜDYOSU');          // true
Stem::same('yazı', 'yazılım');             // false: türemiş kelime ayrı bir kelimedir
Stem::same('agencies', 'agency', 'en');    // true
```

Kök, sözlükteki biçim değil bir karşılaştırma anahtarıdır (*site* → *sit*). Kullanıcıya her zaman özgün kelimeyi
gösterin.

### Anahtar kelimeler: sayfa neyin üzerine kurulu

```php
use Irodev\TurkishText\Keywords;

$report = Keywords::analyze($govdeMetni, 'tr', $baslik, $h1, $metaAciklama);

foreach ($report->keywords as $k) {
    echo "$k->word ×$k->count (%$k->density)", $k->inTitle ? ' · başlıkta' : '', PHP_EOL;
}
// botu ×8 (%19.51) · başlıkta   ← bot, botlar, botları, botu, botların birlikte; en çok kullanılan biçimle gösterilir

foreach ($report->phrases as $p) {
    echo "$p->text ×$p->count", PHP_EOL;          // telegram botları ×3
}

$report->averageSentenceLength;                   // cümle başına kelime
$report->sentenceLength();                        // 'easy' (≤16), 'medium' (≤24), 'hard'
```

### Okunabilirlik

```php
use Irodev\TurkishText\Readability;

$r = Readability::score($paragraflar, 'tr');      // 'tr' için Ateşman, diğerleri için Flesch; 100 kelimenin altında null
if ($r !== null) {
    echo "$r->score/100 · {$r->level()}";         // 52/100 · Orta güçlükte
}
```

| Ateşman | Düzey | | Flesch | Level |
| --- | --- | --- | --- | --- |
| 90–100 | Çok kolay | | 90–100 | Very easy |
| 70–89 | Kolay | | 80–89 | Easy |
| 50–69 | Orta güçlükte | | 70–79 | Fairly easy |
| 30–49 | Zor | | 60–69 | Standard |
| 0–29 | Çok zor | | 50–59 | Fairly difficult |
| | | | 30–49 | Difficult |
| | | | 0–29 | Very difficult |

Sayfanın menülerini ve alt bilgisini değil, paragraf metnini verin: Onlarda cümle yoktur ve sonucu bozarlar.

### Metin yardımcıları

```php
use Irodev\TurkishText\Text;

Text::lower('İSTANBUL IŞIĞI');                    // "istanbul ışığı" (mb_strtolower "i̇stanbul işiği" verir)
Text::words("Google'da e-ticaret sitesi");        // ["google'da", "e-ticaret", "sitesi"]
Text::looksTurkish($metin);                       // lang özelliği eksik ya da yanlış olan sayfalar için
```

## Bilinen sınırlar

Kök bulucu anahtar kelime eşleştirmek için yapıldı ve bilerek basit tutuldu:

- Yalnızca çekim eklerini (çoğul, iyelik, hâl, -ki, -dır) çıkarır. -lık, -lı, -sız, -cı gibi yapım ekleri kalır,
  çünkü yeni bir kelime yaparlar.
- Sondaki *y* katlanmaz; bu yüzden *kolay* ile *kolayı* ayrı kalır (katlansaydı *yapay* ile *yapı* birleşirdi).
- Ünlüden sonraki *n* ve *s* harflerini kaynaştırma harfi saymak, birkaç ilgisiz kelimeyi birleştirir. Örneğin
  *sorun* ile *soru*.

Test dosyası, birleşmesi gereken ve ayrı kalması gereken kelime çiftlerini listeler. Yanlış sonuç veren yaygın bir
kelime bulursanız, çiftiyle birlikte bir issue açmanız çok değerli olur.

## Testler

```bash
composer install
composer test
```

## Kaynaklar

- Ateşman, E. (1997). Türkçede okunabilirliğin ölçülmesi. *Dil Dergisi*, 58, 71–74.
- Flesch, R. (1948). A new readability yardstick. *Journal of Applied Psychology*, 32(3), 221–233.

## Lisans

MIT © [İRODEV LTD](https://irodev.net). İrodev; Türkiye ve Birleşik Krallık'taki işletmeler için web siteleri ve
yazılımlar geliştiren bir stüdyodur. Araçları canlı bir sitede denemek için
[ücretsiz SEO analizi](https://irodev.net/tr/ucretsiz-seo-analizi).
