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
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{
    InputOption,
    InputArgument,
    InputInterface
};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

#[AsCommand(name: 'danilovl:translator:migration-to-database')]
class MigrationToDatabaseCommand extends Command
{
    private readonly SymfonyStyle $io;

    private ?LoggerInterface $logger = null;

    public function __construct(
        private readonly TranslatorConfigurationUtil $translatorConfigurationUtil,
        private readonly MigrationToDatabaseResolver $migrationResolver,
        private readonly LocaleValidator $localeValidator,
        private readonly DomainValidator $domainValidator
    ) {
        parent::__construct();
    }

    public function setLogger(?LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this->setDescription('Migration translations to database.')
            ->addArgument(
                'strategy',
                InputArgument::REQUIRED,
                'Migration strategy.',
                suggestedValues: MigrationToDatabaseStrategyConstant::values()
            )
            ->addOption(
                'mode',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Migration mode.',
                default: MigrationModeConstant::MIGRATE->value,
                suggestedValues: MigrationModeConstant::values()
            )
            ->addOption(
                'locale',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Generate translation for specific locales.',
                suggestedValues: $this->translatorConfigurationUtil->getLocales()
            )
            ->addOption(
                'domain',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Generate translation for specific domain.',
                suggestedValues: $this->translatorConfigurationUtil->getDomains()
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $strategy */
        $strategy = $input->getArgument('strategy');
        /** @var string $mode */
        $mode = $input->getOption('mode');

        /** @var array|null $localeOptions */
        $localeOptions = $input->getOption('locale');
        /** @var array|null $domainOptions */
        $domainOptions = $input->getOption('domain');

        $locales = $this->translatorConfigurationUtil->getLocales();
        $domains = $this->translatorConfigurationUtil->getDomains();

        if (!empty($localeOptions)) {
            $locales = $localeOptions;
        }

        if (!empty($domainOptions)) {
            $domains = $domainOptions;
        }

        $this->localeValidator->validate($locales);
        $this->domainValidator->validate($domains);

        $finder = (new Finder)
            ->files()
            ->in($this->translatorConfigurationUtil->getTranslatorDefaultPath());

        foreach ($locales as $locale) {
            foreach ($domains as $domain) {
                $name = sprintf('%s.%s.yaml', $domain, $locale);
                $finder->name($name);
            }
        }

        try {
            $this->migrationResolver->execute(
                MigrationToDatabaseStrategyConstant::from($strategy),
                MigrationModeConstant::from($mode),
                $finder,
                $this->io,
            );
        } catch (Exception $exception) {
            $message = sprintf('[GenerateTranslationCommand] %s', $exception->getMessage());
            $this->logger?->error($message);

            $this->io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $this->io->success('Done');

        return Command::SUCCESS;
    }
}
