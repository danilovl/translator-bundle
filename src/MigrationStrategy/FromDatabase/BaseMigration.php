<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\MigrationStrategy\FromDatabase;

use Danilovl\TranslatorBundle\Constant\OrderConstant;
use Danilovl\TranslatorBundle\Helper\{
    YamlHelper,
    ArrayHelper
};
use Danilovl\TranslatorBundle\Repository\TranslatorRepository;
use Danilovl\TranslatorBundle\Util\TranslatorConfigurationUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

abstract class BaseMigration
{
    protected SymfonyStyle $io;

    public function __construct(
        protected TranslatorRepository $translatorRepository,
        protected EntityManagerInterface $entityManager,
        protected TranslatorConfigurationUtil $translatorConfigurationUtil
    ) {}

    public function migrate(string $locale, string $domain, SymfonyStyle $io): void
    {
        $this->io = $io;

        $translations = $this->getTranslations($locale, $domain);
        $translations = $this->format($translations, $locale, $domain);

        $this->dumpToFile($translations, $locale, $domain);
    }

    public function dumpYaml(string $locale, string $domain, SymfonyStyle $io): void
    {
        $translations = $this->getTranslations($locale, $domain);
        $translations = $this->format($translations, $locale, $domain);

        $fileName = $this->translatorConfigurationUtil
            ->getTranslationFileName($locale, $domain);

        $io->section($fileName);
        $io->text(YamlHelper::defaultDump($translations));
    }

    protected function getTranslations(string $locale, string $domain): array
    {
        $offset = 0;
        $limit = 500;

        $result = [];

        while (true) {
            $translations = $this->translatorRepository->getKeyValue($locale, $domain, $offset, $limit);
            if (empty($translations)) {
                break;
            }

            $result = array_merge($result, $translations);

            if (count($translations) < $limit) {
                break;
            }

            $offset += $limit;
            $this->entityManager->clear();
        }

        return ArrayHelper::sort($result, OrderConstant::ASCENDING);
    }

    protected function dumpToFile(array $translations, string $locale, string $domain): void
    {
        $fileName = $this->translatorConfigurationUtil
            ->getTranslationFileName($locale, $domain);

        (new Filesystem)->dumpFile($fileName, YamlHelper::defaultDump($translations));
    }

    protected function getFileName(string $locale, string $domain): string
    {
        return sprintf('%s/%s.%s.yaml',
            $this->translatorConfigurationUtil->getTranslatorDefaultPath(),
            $domain,
            $locale
        );
    }

    abstract protected function format(array $translations, string $locale, string $domain): array;
}
