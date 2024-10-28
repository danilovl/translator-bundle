<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Constant;

enum OrderConstant: string
{
    case ASCENDING = 'ASC';
    case DESCENDING = 'DESC';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
