<?php

declare(strict_types=1);

namespace Irodev\TurkishText;

/**
 * Words too common to be keywords, Turkish and English together (pages mix them: menus, brand names, borrowed
 * terms), plus a few URL fragments.
 */
final class Stopwords
{
    /** @return array<string, int> word => index, for isset() lookups */
    public static function all(): array
    {
        static $words = null;
        if ($words === null) {
            $list = 've veya ile için gibi bir bu şu o da de ki mi mı mu mü ne ya çok daha en her hem ise ama fakat ancak olan olarak
                olur oldu olduğu bunu buna bunlar şey sizin bizim size bize siz biz sen ben onlar onun kendi tüm tum diğer sonra önce
                kadar göre üzere değil var yok nasıl neden hangi kim zaman sadece bile ayrıca yani şimdi hemen işte artık hep bütün
                the and for with that this from your you are our not but all can have has will was were they their them its into
                out about more most also than then when what which who how why where here there been being over such only just
                very use using any each per via get got may new one two three los las del com www http https html';
            $words = array_flip(preg_split('/\s+/u', trim($list)) ?: []);
        }
        return $words;
    }

    public static function contains(string $word): bool
    {
        return isset(self::all()[$word]);
    }
}
