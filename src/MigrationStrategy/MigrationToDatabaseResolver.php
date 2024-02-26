<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\MigrationStrategy;

use Danilovl\TranslatorBundle\Constant\{
    MigrationModeConstant,
    MigrationToDatabaseStrategyConstant
};
use Danilovl\TranslatorBundle\MigrationStrategy\ToDatabase\{
    FullMigration,
    GitDiffMigration,
    OnlyNewMigration,
    DatabaseDiffMigration
};
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class MigrationToDatabaseResolver
{
    public function __construct(
        private readonly FullMigration $fullMigration,
        private readonly OnlyNewMigration $onlyNewMigration,
        private readonly GitDiffMigration $gitDiffMigration,
        private readonly DatabaseDiffMigration $databaseDiffMigration
    ) {}

    public function execute(
        MigrationToDatabaseStrategyConstant $strategy,
        MigrationModeConstant $mode,
        Finder $finder,
        SymfonyStyle $io
    ): void {
        switch ($strategy) {
            case MigrationToDatabaseStrategyConstant::FULL:
                if ($mode === MigrationModeConstant::DUMP) {
                    $this->fullMigration->dumpSql($finder, $io);
                } else {
                    $this->fullMigration->migrate($finder, $io);
                }

                break;
            case MigrationToDatabaseStrategyConstant::ONLY_NEW:
                if ($mode === MigrationModeConstant::DUMP) {
                    $this->onlyNewMigration->dumpSql($finder, $io);
                } else {
                    $this->onlyNewMigration->migrate($finder, $io);
                }

                break;
            case MigrationToDatabaseStrategyConstant::GIT_DIFF:
                if ($mode === MigrationModeConstant::DUMP) {
                    $this->gitDiffMigration->dumpSql($finder, $io);
                } else {
                    $this->gitDiffMigration->migrate($finder, $io);
                }

                break;
            case MigrationToDatabaseStrategyConstant::DATABASE_DIFF:
                if ($mode === MigrationModeConstant::DUMP) {
                    $this->databaseDiffMigration->dumpSql($finder, $io);
                } else {
                    $this->databaseDiffMigration->migrate($finder, $io);
                }

                break;
        }
    }
}
