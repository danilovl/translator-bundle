<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Helper;

use Danilovl\TranslatorBundle\Exception\RuntimeException;

class TranslationHelper
{
    public static function getDomainFromFilename(string $filename): string
    {
        $result = explode('.', $filename);
        if (count($result) !== 3) {
            throw new RuntimeException('Can not determine domain from filename.');
        }

        return $result[0];
    }

    public static function getLocaleFromFilename(string $filename): string
    {
        $result = explode('.', $filename);
        if (count($result) !== 3) {
            throw new RuntimeException('Can not determine locale from filename.');
        }

        return $result[1];
    }
}
