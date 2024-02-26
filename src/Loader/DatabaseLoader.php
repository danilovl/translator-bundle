<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Loader;

use Danilovl\TranslatorBundle\Repository\TranslatorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

#[AutoconfigureTag('translation.loader', ['alias' => 'database'])]
readonly class DatabaseLoader implements LoaderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorRepository $translatorRepository
    ) {}

    public function load(mixed $resource, string $locale, string $domain = 'messages'): MessageCatalogue
    {
        $messageCatalogue = new MessageCatalogue($locale);
        $offset = 0;
        $limit = 500;

        while (true) {
            $translations = $this->translatorRepository->getByDomainLocale($locale, $domain, $offset, $limit);
            if (empty($translations)) {
                break;
            }

            foreach ($translations as $translation) {
                $key = $translation->getKey();
                $value = $translation->getValue();
                $domain = $translation->getDomain();

                $messageCatalogue->set($key, $value, $domain);
                $messageCatalogue->setMetadata($key, $value, $domain);
            }

            if (count($translations) < $limit) {
                break;
            }

            $offset += $limit;
            $this->entityManager->clear();
        }

        $this->entityManager->clear();

        return $messageCatalogue;
    }
}
