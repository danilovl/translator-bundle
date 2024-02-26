<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Repository;

use Danilovl\TranslatorBundle\Entity\Translator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;

class TranslatorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Translator::class);
    }

    /**
     * @return Translator[]
     */
    public function getByDomainLocale(
        string $locale,
        string $domain,
        int $offset = null,
        int $limit = null
    ): array {
        $queryBuilder = $this->createQueryBuilder('translator')
            ->where('translator.locale = :locale')
            ->andWhere('translator.domain = :domain')
            ->setParameter('locale', $locale)
            ->setParameter('domain', $domain);

        if ($offset !== null) {
            $queryBuilder->setFirstResult($offset);
        }

        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit);
        }

        /** @var Translator[] $result */
        $result = $queryBuilder->getQuery()->getResult();

        return $result;
    }

    public function getKeyValue(
        string $locale,
        string $domain,
        int $offset = null,
        int $limit = null
    ): array {
        $queryBuilder = $this->createQueryBuilder('translator')
            ->select('translator.key, translator.value')
            ->where('translator.domain = :domain')
            ->andWhere('translator.locale = :locale')
            ->orderBy('translator.key', Criteria::ASC)
            ->setParameter('locale', $locale)
            ->setParameter('domain', $domain);

        if ($offset !== null) {
            $queryBuilder->setFirstResult($offset);
        }

        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit);
        }

        /** @var array<array<string, string>> $translators */
        $translators = $queryBuilder
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);

        $result = [];
        foreach ($translators as $translator) {
            $result[$translator['key']] = $translator['value'];
        }

        return $result;
    }

    /**
     * @return array<string>
     */
    public function getDomains(): array
    {
        $result = $this->createQueryBuilder('translator')
            ->select('translator.domain')
            ->distinct()
            ->getQuery()
            ->getArrayResult();

        return array_column($result, 'domain');
    }
}
