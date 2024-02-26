<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\MigrationStrategy\ToDatabase;

use Danilovl\TranslatorBundle\Helper\TranslationHelper;
use Symfony\Component\Finder\SplFileInfo;

class OnlyNewMigration extends BaseMigration
{
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
        $translations = $this->getTranslationFromFile($file);

        return $this->generateSql(
            '@Translator/migration_strategy/only_new_migration.html.twig',
            $translations,
            $locale,
            $domain
        );
    }

}
