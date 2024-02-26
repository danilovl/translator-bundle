<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\MigrationStrategy\ToDatabase;

use Danilovl\TranslatorBundle\Repository\TranslatorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Danilovl\TranslatorBundle\Helper\{
    ArrayHelper,
    TranslationHelper
};
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

class DatabaseDiffMigration extends BaseMigration
{
    public function __construct(
        EntityManagerInterface $entityManager,
        Environment $twig,
        private readonly TranslatorRepository $translatorRepository
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
        $domain = TranslationHelper::getDomainFromFilename($file->getFilename());

        $previousTranslation = $this->translatorRepository->getKeyValue(
            $locale,
            $domain
        );

        /** @var array $currentTranslation */
        $currentTranslation = Yaml::parseFile((string) $file->getRealPath());
        $currentTranslation = ArrayHelper::flattenArray($currentTranslation);
        ArrayHelper::addEscape($currentTranslation);

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
