<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Command;

use Danilovl\TranslatorBundle\Command\Validator\{
    DomainValidator,
    LocaleValidator
};
use Danilovl\TranslatorBundle\MigrationStrategy\MigrationFromDatabaseResolver;
use Danilovl\TranslatorBundle\Constant\{
    MigrationModeConstant,
    YamlFormatConstant
};
use Danilovl\TranslatorBundle\Util\TranslatorConfigurationUtil;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{
    InputOption,
    InputInterface
};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'danilovl:translator:migration-from-database')]
class MigrationFromDatabaseCommand extends Command
{
    private SymfonyStyle $io;

    private ?LoggerInterface $logger = null;

    public function __construct(
        private readonly TranslatorConfigurationUtil $translatorConfigurationUtil,
        private readonly MigrationFromDatabaseResolver $migrationFromDatabaseResolver,
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
        $this->setDescription('Migration translations from database to yaml.')
            ->addOption(
                'strategy',
                's',
                InputOption::VALUE_OPTIONAL,
                'Migration strategy.',
                default: YamlFormatConstant::FLATTEN->value,
                suggestedValues: YamlFormatConstant::values()
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
                'l',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Generate translation for specific locales.',
                suggestedValues: $this->translatorConfigurationUtil->getLocales()
            )
            ->addOption(
                'domain',
                'd',
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
        $strategy = $input->getOption('strategy');
        /** @var string $mode */
        $mode = $input->getOption('mode');

        $strategy = YamlFormatConstant::from($strategy);
        $mode = MigrationModeConstant::from($mode);

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

        try {
            foreach ($locales as $locale) {
                foreach ($domains as $domain) {
                    $this->migrationFromDatabaseResolver->execute(
                        $strategy,
                        $mode,
                        $locale,
                        $domain,
                        $this->io
                    );
                }
            }
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
