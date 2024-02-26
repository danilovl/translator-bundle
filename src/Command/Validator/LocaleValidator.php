<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Command\Validator;

use Danilovl\TranslatorBundle\Util\TranslatorConfigurationUtil;
use Symfony\Component\Console\Exception\InvalidOptionException;

class LocaleValidator
{
    public function __construct(private readonly TranslatorConfigurationUtil $translatorConfigurationUtil) {}

    public function validate(array $inputLocales): void
    {
        if (empty($inputLocales)) {
            return;
        }

        $locales = $this->translatorConfigurationUtil->getLocales();

        foreach ($inputLocales as $locale) {
            if (!in_array($locale, $locales)) {
                throw new InvalidOptionException(sprintf('Allow locale: %s', implode(', ', $locales)));
            }
        }
    }
}
