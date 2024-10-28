<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\MigrationStrategy\ToDatabase;

use Danilovl\TranslatorBundle\Helper\{
    ArrayHelper,
    TranslationHelper
};
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\{
    Finder,
    SplFileInfo
};
use Symfony\Component\Yaml\Yaml;

class FullMigration extends BaseMigration
{
    public function migrate(Finder $finder, SymfonyStyle $io): void
    {
        $this->entityManager
            ->getConnection()
            ->executeStatement('TRUNCATE translator;');

        parent::migrate($finder, $io);
    }

    protected function execute(SplFileInfo $file): void
    {
        $sql = $this->getSql($file);

        $this->entityManager
            ->getConnection()
            ->executeStatement($sql);
    }

    protected function getSql(SplFileInfo $file): string
    {
        $locale = TranslationHelper::getLocaleFromFilename($file->getFilename());
        $filename = $file->getFilename();
        $domain = TranslationHelper::getDomainFromFilename($filename);

        /** @var array $translations */
        $translations = Yaml::parseFile((string) $file->getRealPath());
        $translations = ArrayHelper::flattenArray($translations);
        ArrayHelper::addEscape($translations);

        return $this->generateSql(
            '@Translator/migration_strategy/full_migration.html.twig',
            $translations,
            $locale,
            $domain
        );
    }
}
