<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Constant;

enum MigrationModeConstant: string
{
    case MIGRATE = 'migrate';
    case DUMP = 'dump';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
