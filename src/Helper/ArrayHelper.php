<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Helper;

use Danilovl\TranslatorBundle\Constant\DiffConstant;
use Danilovl\TranslatorBundle\Constant\OrderConstant;

class ArrayHelper
{
    public static function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix . ($prefix ? '.' : '') . $key;
            if (is_array($value)) {
                $result = array_merge($result, self::flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    public static function dotToNested(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $parts = explode('.', $key);
            $reference = &$result;

            foreach ($parts as $part) {
                if (!isset($reference[$part])) {
                    $reference[$part] = [];
                }
                $reference = &$reference[$part];
            }

            $reference = $value;
        }

        return $result;
    }

    public static function getDiff(array $currentArray, array $previousArray): array
    {
        $diff = [
            DiffConstant::UPDATE->value => [],
            DiffConstant::DELETE->value => array_diff_key($previousArray, $currentArray),
            DiffConstant::INSERT->value => array_diff_key($currentArray, $previousArray)
        ];

        foreach ($currentArray as $key => $value) {
            if (isset($previousArray[$key]) && $previousArray[$key] !== $value) {
                $diff[DiffConstant::UPDATE->value][$key] = $value;
            }
        }

        return $diff;
    }

    /**
     * @param array<array<string>> $translations
     */
    public static function addEscape(array &$translations): void
    {
        foreach ($translations as $key => $translation) {
            $translations[$key] = preg_replace("~'~", "\\'", $translation);
        }
    }

    /**
     * @param array<array<string>> $translations
     */
    public static function sort(array $translations, OrderConstant $order): array
    {
        switch ($order) {
            case OrderConstant::ASCENDING:
                ksort($translations);

                break;
            case OrderConstant::DESCENDING:
                krsort($translations);

                break;
        }

        return $translations;
    }
}
