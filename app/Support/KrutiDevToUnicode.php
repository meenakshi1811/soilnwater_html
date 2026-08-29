<?php

namespace App\Support;

class KrutiDevToUnicode
{
    /**
     * @var list<string>
     */
    private const ARRAY_ONE = [
        'ñ', 'Q+Z', 'sas', 'aa', ')Z', 'ZZ', '‘', '’', '“', '”',
        'å', 'ƒ', '„', '…', '†', '‡', 'ˆ', '‰', 'Š', '‹',
        '¶+', 'd+', '[+k', '[+', 'x+', 'T+', 't+', 'M+', '<+', 'Q+', ';+', 'j+', 'u+',
        'Ùk', 'Ù', 'ä', '–', '—', 'é', '™', '=kk', 'f=k',
        'à', 'á', 'â', 'ã', 'ºz', 'º', 'í', '{k', '{', '=', '«',
        'Nî', 'Vî', 'Bî', 'Mî', '<î', '|', 'K', '}',
        'J', 'Vª', 'Mª', '<ªª', 'Nª', 'Ø', 'Ý', 'nzZ', 'æ', 'ç', 'Á', 'xz', '#', ':',
        'v‚', 'vks', 'vkS', 'vk', 'v', 'b±', 'Ã', 'bZ', 'b', 'm', 'Å', ',s', ',', '_',
        'ô', 'd', 'Dk', 'D', '[k', '[', 'x', 'Xk', 'X', 'Ä', '?k', '?', '³',
        'pkS', 'p', 'Pk', 'P', 'N', 't', 'Tk', 'T', '>', '÷', '¥',
        'ê', 'ë', 'V', 'B', 'ì', 'ï', 'M+', '<+', 'M', '<', '.k', '.',
        'r', 'Rk', 'R', 'Fk', 'F', ')', 'n', '/k', 'èk', '/', 'Ë', 'è', 'u', 'Uk', 'U',
        'i', 'Ik', 'I', 'Q', '¶', 'c', 'Ck', 'C', 'Hk', 'H', 'e', 'Ek', 'E',
        ';', '¸', 'j', 'y', 'Yk', 'Y', 'G', 'o', 'Ok', 'O',
        "'k", "'", '"k', '"', 'l', 'Lk', 'L', 'g',
        'È', 'z',
        'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Ö', 'Ø', 'Ù', 'Ük', 'Ü',
        '‚', 'ks', 'kS', 'k', 'h', 'q', 'w', '`', 's', 'S',
        'a', '¡', '%', 'W', '•', '·', '∙', '·', '~j', '~', '\\', '+', ' ः',
        '^', '*', 'Þ', 'ß', '(', '¼', '½', '¿', 'À', '¾', 'A', '-', '&', '&', 'Œ', ']', '~ ', '@',
    ];

    /**
     * @var list<string>
     */
    private const ARRAY_TWO = [
        '॰', 'QZ+', 'sa', 'a', 'र्द्ध', 'Z', '"', '"', "'", "'",
        '०', '१', '२', '३', '४', '५', '६', '७', '८', '९',
        'फ़्', 'क़', 'ख़', 'ख़्', 'ग़', 'ज़्', 'ज़', 'ड़', 'ढ़', 'फ़', 'य़', 'ऱ', 'ऩ',
        'त्त', 'त्त्', 'क्त', 'दृ', 'कृ', 'न्न', 'न्न्', '=k', 'f=',
        'ह्न', 'ह्य', 'हृ', 'ह्म', 'ह्र', 'ह्', 'द्द', 'क्ष', 'क्ष्', 'त्र', 'त्र्',
        'छ्य', 'ट्य', 'ठ्य', 'ड्य', 'ढ्य', 'द्य', 'ज्ञ', 'द्व',
        'श्र', 'ट्र', 'ड्र', 'ढ्र', 'छ्र', 'क्र', 'फ्र', 'र्द्र', 'द्र', 'प्र', 'प्र', 'ग्र', 'रु', 'रू',
        'ऑ', 'ओ', 'औ', 'आ', 'अ', 'ईं', 'ई', 'ई', 'इ', 'उ', 'ऊ', 'ऐ', 'ए', 'ऋ',
        'क्क', 'क', 'क', 'क्', 'ख', 'ख्', 'ग', 'ग', 'ग्', 'घ', 'घ', 'घ्', 'ङ',
        'चै', 'च', 'च', 'च्', 'छ', 'ज', 'ज', 'ज्', 'झ', 'झ्', 'ञ',
        'ट्ट', 'ट्ठ', 'ट', 'ठ', 'ड्ड', 'ड्ढ', 'ड़', 'ढ़', 'ड', 'ढ', 'ण', 'ण्',
        'त', 'त', 'त्', 'थ', 'थ्', 'द्ध', 'द', 'ध', 'ध', 'ध्', 'ध्', 'ध्', 'न', 'न', 'न्',
        'प', 'प', 'प्', 'फ', 'फ्', 'ब', 'ब', 'ब्', 'भ', 'भ्', 'म', 'म', 'म्',
        'य', 'य्', 'र', 'ल', 'ल', 'ल्', 'ळ', 'व', 'व', 'व्',
        'श', 'श्', 'ष', 'ष्', 'स', 'स', 'स्', 'ह',
        'ीं', '्र',
        'द्द', 'ट्ट', 'ट्ठ', 'ड्ड', 'कृ', 'भ', '्य', 'ड्ढ', 'झ्', 'क्र', 'त्त्', 'श', 'श्',
        'ॉ', 'ो', 'ौ', 'ा', 'ी', 'ु', 'ू', 'ृ', 'े', 'ै',
        'ं', 'ँ', 'ः', 'ॅ', 'ऽ', 'ऽ', 'ऽ', 'ऽ', '्र', '्', '?', '़', ':',
        '‘', '’', '“', '”', ';', '(', ')', '{', '}', '=', '।', '.', '-', 'µ', '॰', ',', '् ', '/',
    ];

    /**
     * @var list<string>
     */
    private const MARKERS = [
        '/\bgSa?\b/',
        '/\bds\b/',
        '/\bdh\b/',
        '/\besa\b/',
        '/\bvk/',
        '/\bfd\b/',
        '/\btks\b/',
        '/\bog\b/',
        '/\bge\b/',
        '/\bdks\b/',
        '/gS\]/',
        '/[a-zA-Z]A(?:\s|$)/',
        '/\bcM\+/',
        '/\brqE/',
        '/\bmlls\b/',
        '/\bgksxk\b/',
        '/\biwjk\b/',
        '/\bvknfe/',
        '/\btkrk\b/',
        '/\bik;k\b/',
        '/\blqukrs\b/',
        '/\bekywe\b/',
        '/\bcuek/',
        '/\b,d\b/',
        '/\bgS/',
        '/\bD;k\b/',
        '/\bugha\b/',
        '/\bgksrk\b/',
        '/\btaxy\b/',
    ];

    public static function looksLike(string $text): bool
    {
        $source = trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($source === '' || mb_strlen($source, 'UTF-8') < 6) {
            return false;
        }

        $letters = preg_match_all('/\p{L}/u', $source) ?: 0;
        $devanagari = preg_match_all('/\p{Devanagari}/u', $source) ?: 0;
        if ($letters > 0 && ($devanagari / $letters) > 0.25) {
            return false;
        }

        if (preg_match_all('/\b(the|and|for|with|this|that|from|have|are|was|not|you|your|will|they|their|been|were|about)\b/i', $source) >= 3) {
            return false;
        }

        $hits = 0;
        foreach (self::MARKERS as $pattern) {
            if (preg_match($pattern, $source) === 1) {
                $hits++;
            }
        }

        $length = mb_strlen($source, 'UTF-8');

        if ($hits >= 3) {
            return true;
        }

        if ($hits >= 2 && $length >= 16) {
            return true;
        }

        return $hits >= 1
            && $length >= 36
            && preg_match('/\bgS/', $source) === 1
            && preg_match('/\b(ds|dh|esa|vk|fd)\b/', $source) === 1;
    }

    public static function convert(string $text): string
    {
        if ($text === '') {
            return $text;
        }

        $modified = str_replace(self::ARRAY_ONE, self::ARRAY_TWO, $text);
        $modified = str_replace(['±', 'Æ'], ['Zं', 'र्f'], $modified);
        $modified = self::movePlaceholder($modified, 'f', 1, 'ि');
        $modified = str_replace(['Ç', 'É'], ['fa', 'र्fa'], $modified);
        $modified = self::movePlaceholder($modified, 'fa', 2, 'िं');
        $modified = str_replace('Ê', 'ीZ', $modified);

        $positionOfWrongEe = mb_strpos($modified, 'ि्', 0, 'UTF-8');
        while ($positionOfWrongEe !== false) {
            $consonant = mb_substr($modified, $positionOfWrongEe + 2, 1, 'UTF-8');
            $search = 'ि्'.$consonant;
            $replacement = '्'.$consonant.'ि';
            $modified = self::replaceAt($modified, $positionOfWrongEe, $search, $replacement);
            $positionOfWrongEe = mb_strpos($modified, 'ि्', $positionOfWrongEe + 2, 'UTF-8');
        }

        $setOfMatras = 'अ आ इ ई उ ऊ ए ऐ ओ औ ा ि ी ु ू ृ े ै ो ौ ं : ँ ॅ';
        $positionOfR = mb_strpos($modified, 'Z', 0, 'UTF-8');
        while ($positionOfR !== false && $positionOfR > 0) {
            $probable = $positionOfR - 1;
            $charAtProbable = mb_substr($modified, $probable, 1, 'UTF-8');
            while ($probable > 0 && mb_strpos($setOfMatras, $charAtProbable, 0, 'UTF-8') !== false) {
                $probable--;
                $charAtProbable = mb_substr($modified, $probable, 1, 'UTF-8');
            }

            $chunk = mb_substr($modified, $probable, $positionOfR - $probable, 'UTF-8');
            $modified = mb_substr($modified, 0, $probable, 'UTF-8')
                .'र्'.$chunk
                .mb_substr($modified, $positionOfR + 1, null, 'UTF-8');
            $positionOfR = mb_strpos($modified, 'Z', 0, 'UTF-8');
        }

        return $modified;
    }

    public static function convertIfNeeded(string $text): string
    {
        if ($text === '' || ! self::looksLike($text)) {
            return $text;
        }

        if (! str_contains($text, '<')) {
            return self::convert($text);
        }

        return preg_replace_callback('/>([^<]*)</u', function (array $matches): string {
            $chunk = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (trim($chunk) === '') {
                return $matches[0];
            }

            return '>'.htmlspecialchars(self::convert($chunk), ENT_QUOTES | ENT_HTML5, 'UTF-8').'<';
        }, $text) ?? $text;
    }

    private static function movePlaceholder(string $modified, string $needle, int $skipChars, string $matra): string
    {
        $position = mb_strpos($modified, $needle, 0, 'UTF-8');
        $needleLength = mb_strlen($needle, 'UTF-8');

        while ($position !== false) {
            $next = mb_substr($modified, $position + $needleLength, 1, 'UTF-8');
            $search = $needle.$next;
            $replacement = $next.$matra;
            $modified = self::replaceAt($modified, $position, $search, $replacement);
            $position = mb_strpos($modified, $needle, $position + mb_strlen($replacement, 'UTF-8'), 'UTF-8');
        }

        return $modified;
    }

    private static function replaceAt(string $haystack, int $position, string $search, string $replacement): string
    {
        return mb_substr($haystack, 0, $position, 'UTF-8')
            .$replacement
            .mb_substr($haystack, $position + mb_strlen($search, 'UTF-8'), null, 'UTF-8');
    }
}
