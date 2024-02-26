<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Constant;

enum MigrationToDatabaseStrategyConstant: string
{
    case FULL = 'full';
    case ONLY_NEW = 'only-new';
    case GIT_DIFF = 'git-diff';
    case DATABASE_DIFF = 'database-diff';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
