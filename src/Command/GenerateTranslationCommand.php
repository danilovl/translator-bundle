<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Command;

use Danilovl\TranslatorBundle\Command\Validator\LocaleValidator;
use Danilovl\TranslatorBundle\Service\TranslationCacheService;
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

#[AsCommand(name: 'danilovl:translator:generate-translation')]
class GenerateTranslationCommand extends Command
{
    private SymfonyStyle $io;

    private ?LoggerInterface $logger = null;

    public function __construct(
        private readonly TranslatorConfigurationUtil $translatorConfigurationUtil,
        private readonly TranslationCacheService $translationCacheService,
        private readonly LocaleValidator $localeValidator
    ) {
        parent::__construct();
    }

    public function setLogger(?LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this->setDescription('Generation translations.')
            ->addOption(
                'locale',
                'l',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Generate translations for specific locales.',
                suggestedValues: $this->translatorConfigurationUtil->getLocales()
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array|null $optionLocales */
        $optionLocales = $input->getOption('locale');

        if (!empty($optionLocales)) {
            $locales = $optionLocales;
        } else {
            $locales = $this->translatorConfigurationUtil->getLocales();
        }

        $this->localeValidator->validate($locales);

        foreach ($locales as $locale) {
            try {
                $this->generateTranslation(
                    $locale,
                    $this->translatorConfigurationUtil->getTranslationsKernelCacheDir()
                );
            } catch (Exception $exception) {
                $message = sprintf('[GenerateTranslationCommand] %s', $exception->getMessage());
                $this->logger?->error($message);

                $this->io->error($exception->getMessage());

                return Command::FAILURE;
            }
        }

        $this->io->success('Done');

        return Command::SUCCESS;
    }

    private function generateTranslation(string $locale, string $translationCacheDir): void
    {
        $info = sprintf('Clearing translations cache for locale "%s".', $locale);
        $this->io->note($info);
        $this->translationCacheService->clear($locale, $translationCacheDir);

        $info = sprintf('Generating translation for locale "%s".', $locale);
        $this->io->note($info);
        $this->translationCacheService->warmUpCatalogue($locale);
    }
}
