<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Farm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

class FarmRepository extends ServiceEntityRepository
{
    private Connection $connection;

    public function __construct(ManagerRegistry $registry, Connection $connection)
    {
        parent::__construct($registry, Farm::class);
        $this->connection = $connection;
    }

    public function update(array $farms): void
    {
        $this->connection->beginTransaction();

        $currentDate = date_create()->format('Y-m-d H:i:00');

        foreach ($farms as $farm) {

            $sql = "INSERT INTO farm (hash, farm_id, created_at, last_found_at, updated_at, json, name, tvl, token, chain, compound, leverage, inactive, deprecated) VALUES (:hash, :farm_id, :created_at, :last_found_at, :updated_at, :json, :name, :tvl, :token, :chain, :compound, :leverage, :inactive, :deprecated) "
                . "ON CONFLICT(farm_id) DO UPDATE SET last_found_at = :last_found_at, json = :json, updated_at = :updated_at, name = :name, tvl = :tvl, token = :token, hash = :hash, chain = :chain, compound = :compound, leverage = :leverage, inactive = :inactive, deprecated = :deprecated";

            $stmt = $this->connection->prepare($sql);

            $stmt->bindValue('farm_id', $farm['id']);
            $stmt->bindValue('hash', md5($farm['id']));
            $stmt->bindValue('name', $farm['name'] ?? null);
            $stmt->bindValue('tvl', $farm['tvl']['usd'] ?? null);
            $stmt->bindValue('chain', $farm['chain'] ?? null);
            $stmt->bindValue('compound', $farm['compound'] ?? false);
            $stmt->bindValue('leverage', $farm['leverage'] ?? false);
            $stmt->bindValue('created_at', $currentDate);
            $stmt->bindValue('last_found_at', $currentDate);
            $stmt->bindValue('updated_at', $currentDate);
            $stmt->bindValue('json', json_encode($farm));
            $stmt->bindValue('token', isset($farm['extra']['transactionToken']) ? strtolower($farm['extra']['transactionToken']) : null);

            $stmt->bindValue('inactive', ($farm['inactive'] ?? false) || ($farm['flags']['inactive'] ?? false));
            $stmt->bindValue('deprecated', ($farm['deprecated'] ?? false) || ($farm['flags']['deprecated'] ?? false));

            $stmt->execute();
        }

        $this->connection->commit();
    }

    public function getNewFarmsTimeline(): array
    {
        $qb = $this->createQueryBuilder('f', 'f.farmId');

        $qb->orderBy('f.createdAt', 'DESC');
        $qb->setMaxResults(300);

        return $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 10)
            ->setResultCacheId('new-farms-v2-timeline')
            ->getArrayResult();
    }

    /**
     * @return string[]
     */
    public function getNewFarm(): array
    {
        $qb = $this->createQueryBuilder('f');

        $qb->orderBy('f.createdAt', 'DESC');
        $qb->setMaxResults(20);

        return $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 5)
            ->setResultCacheId('new-farms-v4-content')
            ->getArrayResult();
    }

    public function getFarmHashes(): array
    {
        $qb = $this->createQueryBuilder('f');

        $qb->select('f.hash', 'f.updatedAt');
        $qb->andWhere('f.lastFoundAt <= :now');
        $qb->andWhere('f.lastFoundAt >= :lastFoundAtWindow');

        $qb->setParameter('now', date_create()->modify('+2 hours'));
        $qb->setParameter('lastFoundAtWindow', $this->findLastSyncWindow());

        return $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 60)
            ->setResultCacheId('getFarmHashes')
            ->getArrayResult();
    }

    public function getFarmTokens(): array
    {
        $qb = $this->createQueryBuilder('f');

        $qb->select('f.token', 'f.updatedAt');
        $qb->andWhere('f.token is not null');
        $qb->andWhere('f.lastFoundAt <= :now');
        $qb->andWhere('f.lastFoundAt >= :lastFoundAtWindow');

        $qb->groupBy('f.token');

        $qb->setParameter('now', date_create()->modify('+2 hours'));
        $qb->setParameter('lastFoundAtWindow', $this->findLastSyncWindow());

        return $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 60)
            ->setResultCacheId('getFarmHashes')
            ->getArrayResult();
    }

    public function findLastSyncWindow(): \DateTimeInterface
    {
        $qb = $this->createQueryBuilder('f');
        $qb->select('max(f.lastFoundAt)');
        $qb->andWhere('f.lastFoundAt <= :now');
        $qb->andWhere('f.lastFoundAt >= :lastFoundAtWindow');

        $qb->setParameter('now', date_create()->modify('+2 hours'));
        $qb->setParameter('lastFoundAtWindow', date_create()->modify('-3 days'));

        $result = $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 5)
            ->setResultCacheId('last-sync-window')
            ->getSingleScalarResult();

        if (!$result) {
            $result = date_create()->modify('-3 days');
        } else {
            $result = new \DateTimeImmutable($result);
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
            ->setResultCacheId('farm-by-v2-token' . md5($token))
            ->getArrayResult();
    }

    public function findFarmIdByHash(string $hash): ?Farm
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
            ->setResultCacheId('farm-by-v3-hash' . md5($hash))
            ->getOneOrNullResult();

        return $result;
    }

    /**
     * @return array[]
     */
    public function getTvl(): array
    {
        $qb = $this->createQueryBuilder('f');

        $qb->andWhere('f.tvl > 0');

        $qb->andWhere('f.lastFoundAt >= :lastFoundAtWindow');
        $qb->setParameter('lastFoundAtWindow', $this->findLastSyncWindow());

        $qb->orderBy('f.tvl', 'DESC');
        $qb->setMaxResults(16);

        return $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 30)
            ->setResultCacheId('tvl-farms-v6-content')
            ->getArrayResult();
    }

    /**
     * @return array[]
     */
    public function getAllValid(): array
    {
        $qb = $this->createQueryBuilder('f');

        $qb->orderBy('f.tvl', 'DESC');

        $qb->andWhere('f.lastFoundAt >= :lastFoundAtWindow');
        $qb->setParameter('lastFoundAtWindow', $this->findLastSyncWindow());

        return $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 5)
            ->setResultCacheId('all-valid-farms-v2-content')
            ->getArrayResult();
    }
}
