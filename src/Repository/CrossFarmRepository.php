<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\CrossFarm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

class CrossFarmRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CrossFarm::class);
    }

    public function update(array $farms): void
    {
        $connection = $this->_em->getConnection();
        $connection->beginTransaction();

        $currentDate = date_create()->format('Y-m-d H:i:00');

        $index = 0;
        foreach ($farms as $farm) {

            $sql = "INSERT INTO cross_farm (hash, farm_id, created_at, last_found_at, updated_at, json, name, tvl, token, chain, compound, leverage, inactive, deprecated, provider, bond, amm, ib_token, apy, earns, stable) VALUES (:hash, :farm_id, :created_at, :last_found_at, :updated_at, :json, :name, :tvl, :token, :chain, :compound, :leverage, :inactive, :deprecated, :provider, :bond, :amm, :ib_token, :apy, :earns, :stable) "
                . "ON CONFLICT(farm_id) DO UPDATE SET last_found_at = :last_found_at, json = :json, updated_at = :updated_at, name = :name, tvl = :tvl, token = :token, hash = :hash, chain = :chain, compound = :compound, leverage = :leverage, inactive = :inactive, deprecated = :deprecated, provider = :provider, bond = :bond, amm = :amm, ib_token = :ib_token, apy = :apy, earns = :earns, stable = :stable";

            $stmt = $connection->prepare($sql);

            $tvlUsd = $farm['tvl']['usd'] ?? null;

            // secure
            if ($tvlUsd && $tvlUsd > 10198612188) {
                $tvlUsd = null;
            }

            $stmt->bindValue('farm_id', $farm['id']);
            $stmt->bindValue('hash', md5($farm['id']));
            $stmt->bindValue('name', $farm['name'] ?? null);
            $stmt->bindValue('tvl', $tvlUsd);
            $stmt->bindValue('provider', $farm['provider']);
            $stmt->bindValue('chain', $farm['chain'] ?? null);
            $stmt->bindValue('compound', $farm['compound'] ?? false);
            $stmt->bindValue('leverage', $farm['leverage'] ?? false);
            $stmt->bindValue('created_at', $currentDate);
            $stmt->bindValue('last_found_at', $currentDate);
            $stmt->bindValue('updated_at', $currentDate);
            $stmt->bindValue('json', json_encode($farm));
            $stmt->bindValue('token', isset($farm['extra']['transactionToken']) ? strtolower($farm['extra']['transactionToken']) : null);

            $stmt->bindValue('inactive', ($farm['inactive'] ?? false) || in_array('inactive', $farm['flags'] ?? [], true));
            $stmt->bindValue('deprecated', ($farm['deprecated'] ?? false) || in_array('deprecated', $farm['flags'] ?? [], true));
            $stmt->bindValue('bond', in_array('bond', $farm['flags'] ?? [], true));
            $stmt->bindValue('amm',
                in_array('lend', $farm['flags'] ?? [], true)
                || in_array('borrow', $farm['flags'] ?? [], true)
            );

            $stmt->bindValue('ib_token', in_array('ibToken', $farm['flags'] ?? [], true));
            $stmt->bindValue('apy', $farm['yield']['apy'] ?? null);

            $stmt->bindValue('earns', count($farm['earns'] ?? []) > 0 || count($farm['earn'] ?? []));

            $stmt->bindValue('stable', in_array('stable', $farm['flags'] ?? [], true));

            $stmt->execute();

            if ($index++ % 100 == 0 && $connection->isTransactionActive()) {
                $connection->commit();
            }
        }

        if ($connection->isTransactionActive()) {
            $connection->commit();
        }
    }

    public function getNewFarmsTimeline(array $chains = []): array
    {
        $sortedChains = $chains;
        asort($sortedChains);

        $qb = $this->createQueryBuilder('f', 'f.farmId');

        if (count($sortedChains) > 0) {
            $qb->andWhere('f.chain IN (:chains)');
            $qb->setParameter('chains', $sortedChains);
        }

        $qb->orderBy('f.createdAt', 'DESC');
        $qb->setMaxResults(300);

        return $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 10)
            ->setResultCacheId('new-crossfarms-v2-timeline-' . md5(json_encode($chains)))
            ->getArrayResult();
    }

    /**
     * @return string[]
     */
    public function getNewFarm(string $chain): array
    {
        $qb = $this->createQueryBuilder('f');

        $qb->andWhere('f.chain = :chain');
        $qb->setParameter('chain', $chain);

        $qb->orderBy('f.createdAt', 'DESC');
        $qb->setMaxResults(20);

        return $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 5)
            ->setResultCacheId('new-crossfarms-v5-content-' . $chain)
            ->getArrayResult();
    }

    /**
     * @return string[]
     */
    public function getAllNewFarm(int $max): array
    {
        $qb = $this->createQueryBuilder('f');

        $qb->orderBy('f.createdAt', 'DESC');
        $qb->setMaxResults($max);

        return $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 5)
            ->setResultCacheId('new-crossfarms-v4-content-' . $max)
            ->getArrayResult();
    }

    public function getFarmHashes(): array
    {
        $qb = $this->createQueryBuilder('f');

        $qb->select('f.hash', 'f.updatedAt');
        $qb->andWhere('f.lastFoundAt <= :now');
        $qb->andWhere('f.lastFoundAt >= :lastFoundAtWindow');

        $qb->setParameter('now', new \DateTime(date_create()->modify('+2 hours')->format('Y-m-d H:00:00')));
        $qb->setParameter('lastFoundAtWindow', $this->findLastSyncWindow());

        return $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 60)
            ->setResultCacheId('getFarmHashes-cross')
            ->getArrayResult();
    }

    public function getFarmTokens(): array
    {
        $qb = $this->createQueryBuilder('f');

        $qb->select('f.token', 'f.updatedAt', 'f.chain');
        $qb->andWhere('f.token is not null');
        $qb->andWhere('f.lastFoundAt <= :now');
        $qb->andWhere('f.lastFoundAt >= :lastFoundAtWindow');

        $qb->groupBy('f.token');

        $qb->setParameter('now', new \DateTime(date_create()->modify('+2 hours')->format('Y-m-d H:00:00')));
        $qb->setParameter('lastFoundAtWindow', $this->findLastSyncWindow());

        return $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 60)
            ->setResultCacheId('getFarmHashes-cross')
            ->getArrayResult();
    }

    public function findLastSyncWindow(): \DateTimeInterface
    {
        $qb = $this->createQueryBuilder('f');
        $qb->select('max(f.lastFoundAt)');
        $qb->andWhere('f.lastFoundAt <= :now');
        $qb->andWhere('f.lastFoundAt >= :lastFoundAtWindow');

        $qb->setParameter('now', new \DateTime(date_create()->modify('+2 hours')->format('Y-m-d H:00:00')));
        $qb->setParameter('lastFoundAtWindow', new \DateTime(date_create()->modify('-30 days')->format('Y-m-d H:00:00')));

        $result = $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 30)
            ->setResultCacheId('last-sync-window-cross-v1')
            ->getSingleScalarResult();

        if (!$result) {
            $result = date_create()->modify('-5 days midnight');
        } else {
            $result = (new \DateTimeImmutable($result))->modify('-3 day midnight');
        }

        return new \DateTimeImmutable($result->format('Y-m-d H:00:00'));
    }

    /**
     * @return array[]
     */
    public function findFarmIdsByToken(string $token, string $selfFarmId = null): array
    {
        $qb = $this->createQueryBuilder('f');

        $qb->andWhere('f.token = :token');
        $qb->setParameter('token', $token);

        $qb->andWhere('f.lastFoundAt >= :lastFoundAtWindow');
        $qb->setParameter('lastFoundAtWindow', $this->findLastSyncWindow());

        $qb->orderBy('f.tvl', 'DESC');
        $qb->setMaxResults(50);

        if ($selfFarmId) {
            $qb->andWhere('f.farmId != :id');
            $qb->setParameter('id', $selfFarmId);
        }

        return $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 10)
            ->setResultCacheId('farm-by-v2-token-cross' . md5($token))
            ->getArrayResult();
    }

    public function findFarmIdByHash(string $hash): ?CrossFarm
    {
        $qb = $this->createQueryBuilder('f', 'f.farmId');

        $qb->andWhere('f.hash = :hash');
        $qb->setParameter('hash', $hash);

        $qb->andWhere('f.lastFoundAt >= :lastFoundAtWindow');
        $qb->setParameter('lastFoundAtWindow', $this->findLastSyncWindow());

        $qb->setMaxResults(1);

        $result = $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 10)
            ->setResultCacheId('farm-by-v3-hash-cross' . md5($hash))
            ->getOneOrNullResult();

        return $result;
    }

    /**
     * @return array[]
     */
    public function getTvl(string $chain): array
    {
        $qb = $this->createQueryBuilder('f');

        $qb->andWhere('f.tvl > 0');

        $qb->andWhere('f.lastFoundAt >= :lastFoundAtWindow');
        $qb->setParameter('lastFoundAtWindow', $this->findLastSyncWindow());

        $qb->andWhere('f.chain = :chain');
        $qb->setParameter('chain', $chain);

        $qb->orderBy('f.tvl', 'DESC');
        $qb->setMaxResults(16);

        return $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 30)
            ->setResultCacheId('tvl-crossfarms-v6-content-' . $chain)
            ->getArrayResult();
    }

    /**
     * @return array[]
     */
    public function getAllValid(string $chain): array
    {
        $qb = $this->createQueryBuilder('f');

        $qb->orderBy('f.tvl', 'DESC');

        $qb->andWhere('f.lastFoundAt >= :lastFoundAtWindow');
        $qb->setParameter('lastFoundAtWindow', $this->findLastSyncWindow());

        $qb->andWhere('f.chain = :chain');
        $qb->setParameter('chain', $chain);

        return $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 30)
            ->setResultCacheId('all-valid-crossfarms-v3-content-' . $chain)
            ->getArrayResult();
    }

    public function search(array $platforms, array $chains, ?string $query, array $tags, int $page, ?string $sort): Query
    {
        // cache hit
        sort($platforms);
        sort($chains);
        sort($tags);
        $query = $query ? strtolower($query) : null;

        $md5 = md5(json_encode([
            $platforms,
            $chains,
            $query,
            $tags,
            $page,
            $sort
        ]));

        $qb = $this->createQueryBuilder('f');

        if ($sort === 'tvl_desc') {
            $qb->orderBy('f.tvl', 'DESC');
        }

        if ($sort === 'tvl_asc') {
            $qb->orderBy('f.tvl', 'ASC');
        }

        if ($sort === 'apy_desc') {
            $qb->orderBy('f.apy', 'DESC');
        }

        if ($sort === 'apy_asc') {
            $qb->orderBy('f.apy', 'ASC');
        }

        if ($sort === 'name_desc') {
            $qb->orderBy('f.name', 'DESC');
        }

        if ($sort === 'name_asc') {
            $qb->orderBy('f.name', 'ASC');
        }

        if ($sort === 'provider_desc') {
            $qb->orderBy('f.provider', 'DESC');
        }

        if ($sort === 'provider_asc') {
            $qb->orderBy('f.provider', 'ASC');
        }

        $qb->andWhere('f.lastFoundAt >= :lastFoundAtWindow');
        $qb->setParameter('lastFoundAtWindow', $this->findLastSyncWindow());

        if (count($platforms) > 0) {
            $qb->andWhere('f.provider IN (:provider)');
            $qb->setParameter('provider', $platforms);
        }

        if (count($chains) > 0) {
            $qb->andWhere('f.chain IN (:chain)');
            $qb->setParameter('chain', $chains);
        }

        if ($query && strlen($query) < 150) {
            $q1 = [];

            $q1[] = $qb->expr()->like('f.name', ':query');
            $qb->setParameter('query', '%' . $query . '%');

            if (strlen($query) > 3 && preg_match('#^[0x]?.[0-9a-f]{3,}$#i', $query)) {
                $q1[] = $qb->expr()->like('f.token', ':token');
                $q1[] = $qb->expr()->like('f.token0', ':token');
                $q1[] = $qb->expr()->like('f.token1', ':token');

                $qb->setParameter('token', '%' . $query . '%');
            }

            $qb->andWhere($qb->expr()->orX(...$q1));
        }

        $q = [];
        if (in_array('bond', $tags, true)) {
            $q[] = $qb->expr()->eq('f.bond', true);
        }

        if (in_array('auto_compound', $tags, true)) {
            $q[] = $qb->expr()->eq('f.compound', true);
        }

        if (in_array('lend_borrow', $tags, true)) {
            $q[] = $qb->expr()->eq('f.amm', true);
        }

        if (in_array('ib_token', $tags, true)) {
            $q[] = $qb->expr()->eq('f.ibToken', true);
        }

        if (in_array('earns', $tags, true)) {
            $q[] = $qb->expr()->eq('f.earns', true);
        }

        if (in_array('leverage', $tags, true)) {
            $q[] = $qb->expr()->eq('f.leverage', true);
        }

        if (in_array('stable', $tags, true)) {
            $q[] = $qb->expr()->eq('f.stable', true);
        }

        if (count($q) > 0) {
            $qb->andWhere($qb->expr()->orX(...$q));
        }

        return $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 30)
            ->setResultCacheId('farm-search-backend-' . $md5);
    }
}
