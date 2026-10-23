# Changelog

## 1.0.0 – 2026-10-23

First release, extracted from the text analysis behind the free SEO audit at [irodev.net](https://irodev.net).

- `TurkishStemmer`: inflectional suffixes with vowel harmony, buffer letters and consonant softening.
- `EnglishStemmer`: plurals and possessives.
- `Readability`: Ateşman for Turkish, Flesch Reading Ease for English, with their bands.
- `Keywords`: keyword density and repeated phrases with word forms counted together; title, H1 and description
  matches by stem; the shorter form wins a tie.
- `Text`: Turkish-aware lower-casing, word splitting, Turkish detection; `Stopwords` for Turkish and English.
