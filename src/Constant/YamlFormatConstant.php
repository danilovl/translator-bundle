<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Constant;

enum YamlFormatConstant: string
{
    case FLATTEN = 'flatten';
    case DOT_NESTED = 'dotNested';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
