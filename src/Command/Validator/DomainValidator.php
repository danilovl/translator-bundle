<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Command\Validator;

use Danilovl\TranslatorBundle\Util\TranslatorConfigurationUtil;
use Symfony\Component\Console\Exception\InvalidOptionException;

class DomainValidator
{
    public function __construct(private readonly TranslatorConfigurationUtil $translatorConfigurationUtil) {}

    public function validate(array $inputDomains): void
    {
        if (empty($inputDomains)) {
            return;
        }

        $domains = $this->translatorConfigurationUtil->getDomains();

        foreach ($inputDomains as $locale) {
            if (!in_array($locale, $domains)) {
                throw new InvalidOptionException(sprintf('Allow domain: %s', implode(', ', $domains)));
            }
        }
    }
}
