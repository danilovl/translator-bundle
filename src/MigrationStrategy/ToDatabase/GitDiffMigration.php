<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\MigrationStrategy\ToDatabase;

use Danilovl\TranslatorBundle\Util\TranslatorConfigurationUtil;
use Doctrine\ORM\EntityManagerInterface;
use Danilovl\TranslatorBundle\Helper\{
    GitHelper,
    ArrayHelper,
    TranslationHelper
};
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

class GitDiffMigration extends BaseMigration
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected Environment $twig,
        private readonly TranslatorConfigurationUtil $translatorConfigurationUtil
    ) {
        parent::__construct($entityManager, $twig);
    }

    protected function execute(SplFileInfo $file): void
    {
        $sql = $this->getSql($file);

        if (empty($sql)) {
            $this->io->warning('Nothing to migrate.');

            return;
        }

        $this->entityManager
            ->getConnection()
            ->executeStatement($sql);
    }

    protected function getSql(SplFileInfo $file): string
    {
        $locale = TranslationHelper::getLocaleFromFilename($file->getFilename());

        $filePath = str_replace(
            $this->translatorConfigurationUtil->getKernelProjectDir() . '/',
            '',
            $file->getRealPath()
        );
        $previousTranslation = GitHelper::getShowLatestFileContent($filePath);

        /** @var array $currentTranslation */
        $currentTranslation = Yaml::parseFile((string) $file->getRealPath());
        $currentTranslation = ArrayHelper::flattenArray($currentTranslation);
        ArrayHelper::addEscape($currentTranslation);

        $filename = $file->getFilename();
        $domain = TranslationHelper::getDomainFromFilename($filename);

        $result = ArrayHelper::getDiff($currentTranslation, $previousTranslation);

        $sql = '';

        foreach ($result as $mode => $value) {
            if (empty($value)) {
                continue;
            }

            $sql .= $this->generateDiffSql($mode, $value, $locale, $domain) . "\n";

        }

        return $sql;
    }
}
