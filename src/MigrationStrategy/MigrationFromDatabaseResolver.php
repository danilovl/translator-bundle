<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\MigrationStrategy;

use Danilovl\TranslatorBundle\Constant\{
    YamlFormatConstant,
    MigrationModeConstant
};
use Danilovl\TranslatorBundle\MigrationStrategy\FromDatabase\{
    FlattenMigration,
    DotNestedMigration
};
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrationFromDatabaseResolver
{
    public function __construct(
        private readonly FlattenMigration $flattenMigration,
        private readonly DotNestedMigration $dotNestedMigration,
    ) {}

    public function execute(
        YamlFormatConstant $strategy,
        MigrationModeConstant $mode,
        string $locale,
        string $domain,
        SymfonyStyle $io
    ): void {
        switch ($strategy) {
            case YamlFormatConstant::FLATTEN:
                if ($mode === MigrationModeConstant::DUMP) {
                    $this->flattenMigration->dumpYaml($locale, $domain, $io);
                } else {
                    $this->flattenMigration->migrate($locale, $domain, $io);
                }

                break;
            case YamlFormatConstant::DOT_NESTED:
                if ($mode === MigrationModeConstant::DUMP) {
                    $this->dotNestedMigration->dumpYaml($locale, $domain, $io);
                } else {
                    $this->dotNestedMigration->migrate($locale, $domain, $io);
                }

                break;
        }
    }
}
