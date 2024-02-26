<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\MigrationStrategy\ToDatabase;

use Danilovl\TranslatorBundle\Constant\DiffConstant;
use Danilovl\TranslatorBundle\Helper\ArrayHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\{
    Finder,
    SplFileInfo
};
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

abstract class BaseMigration
{
    protected SymfonyStyle $io;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected Environment $twig
    ) {}

    public function migrate(Finder $finder, SymfonyStyle $io): void
    {
        $this->io = $io;

        foreach ($finder as $file) {
            $io->section('Start sync: ' . $file->getFilename());

            $this->execute($file);
        }
    }

    public function dumpSql(Finder $finder, SymfonyStyle $io): void
    {
        $this->io = $io;

        foreach ($finder as $file) {
            $io->section('Start dump: ' . $file->getFilename());

            $sql = $this->getSql($file);
            if (empty($sql)) {
                $this->io->warning('SQL is empty.');

                continue;
            }

            $io->text($sql);
        }
    }

    protected function getTranslationFromFile(SplFileInfo $file): array
    {
        /** @var array $translations */
        $translations = Yaml::parseFile((string) $file->getRealPath());
        $translations = ArrayHelper::flattenArray($translations);
        ArrayHelper::addEscape($translations);

        return $translations;
    }

    protected function generateSql(
        string $template,
        array $translations,
        string $locale,
        string $domain
    ): string {
        return $this->twig->render($template, [
            'locale' => $locale,
            'domain' => $domain,
            'translations' => $translations
        ]);
    }

    /**
     * @param array<array<string>> $translations
     */
    protected function generateDiffSql(string $mode, array $translations, string $locale, string $domain): string
    {
        $mode = DiffConstant::from($mode);

        $template = match ($mode) {
            DiffConstant::UPDATE => '@Translator/migration_strategy/update_translation.html.twig',
            DiffConstant::DELETE => '@Translator/migration_strategy/delete_translation.html.twig',
            DiffConstant::INSERT => '@Translator/migration_strategy/insert_translation.html.twig',
        };

        return $this->generateSql(
            $template,
            $translations,
            $locale,
            $domain
        );
    }

    abstract protected function execute(SplFileInfo $file): void;

    abstract protected function getSql(SplFileInfo $file): string;
}
