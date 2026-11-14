<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Command;

use Danilovl\TranslatorBundle\Command\Validator\{
    DomainValidator,
    LocaleValidator
};
use Danilovl\TranslatorBundle\Constant\{
    MigrationModeConstant,
    MigrationToDatabaseStrategyConstant
};
use Danilovl\TranslatorBundle\MigrationStrategy\MigrationToDatabaseResolver;
use Danilovl\TranslatorBundle\Util\TranslatorConfigurationUtil;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\{
    Option,
    Argument,
    AsCommand
};
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'danilovl:translator:migration-to-database',
    description: 'Migration translations to database.'
)]
class MigrationToDatabaseCommand
{
    private ?LoggerInterface $logger = null;

    public function __construct(
        private readonly TranslatorConfigurationUtil $translatorConfigurationUtil,
        private readonly MigrationToDatabaseResolver $migrationResolver,
        private readonly LocaleValidator $localeValidator,
        private readonly DomainValidator $domainValidator
    ) {}

    public function setLogger(?LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param string[] $locale
     * @param string[] $domain
     */
    public function __invoke(
        SymfonyStyle $io,
        #[Argument(description: 'Migration strategy.')]
        MigrationToDatabaseStrategyConstant $strategy,
        #[Option(description: 'Migration mode.', shortcut: 'm')]
        MigrationModeConstant $mode = MigrationModeConstant::MIGRATE,
        #[Option(description: 'Generate translation for specific locales.', suggestedValues: [self::class, 'getLocales'])]
        array $locale = [],
        #[Option(description: 'Generate translation for specific domain.', suggestedValues: [self::class, 'getDomains'])]
        array $domain = []
    ): int {
        $locales = !empty($locale) ? $locale : $this->translatorConfigurationUtil->getLocales();
        $domains = !empty($domain) ? $domain : $this->translatorConfigurationUtil->getDomains();

        $this->localeValidator->validate($locales);
        $this->domainValidator->validate($domains);

        $finder = new Finder()
            ->files()
            ->in($this->translatorConfigurationUtil->getTranslatorDefaultPath());

        foreach ($locales as $localeItem) {
            foreach ($domains as $domainItem) {
                $finder->name(sprintf('%s.%s.yaml', $domainItem, $localeItem));
            }
        }

        try {
            $this->migrationResolver->execute($strategy, $mode, $finder, $io);
        } catch (Exception $exception) {
            $message = sprintf('[MigrationToDatabaseCommand] %s', $exception->getMessage());
            $this->logger?->error($message);

            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->success('Done');

        return Command::SUCCESS;
    }

    /**
     * @return string[]
     */
    public function getLocales(): array
    {
        return $this->translatorConfigurationUtil->getLocales();
    }

    /**
     * @return string[]
     */
    public function getDomains(): array
    {
        return $this->translatorConfigurationUtil->getDomains();
    }
}
