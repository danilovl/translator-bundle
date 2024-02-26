<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\MigrationStrategy\FromDatabase;

use Danilovl\TranslatorBundle\Helper\ArrayHelper;

class FlattenMigration extends BaseMigration
{
    public function format(array $translations, string $locale, string $domain): array
    {
        return ArrayHelper::flattenArray($translations);
    }
}
