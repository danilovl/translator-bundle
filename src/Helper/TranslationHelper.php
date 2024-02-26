<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Helper;

use Danilovl\TranslatorBundle\Exception\RuntimeException;

class TranslationHelper
{
    public static function getDomainFromFilename(string $filename): string
    {
        $domain = explode('.', $filename)[0] ?? null;
        if ($domain === null) {
            throw new RuntimeException('Can not determine domain from filename.');
        }

        return $domain;
    }

    public static function getLocaleFromFilename(string $filename): string
    {
        $locale = explode('.', $filename)[1] ?? null;
        if ($locale === null) {
            throw new RuntimeException('Can not determine locale from filename.');
        }

        return $locale;
    }
}
