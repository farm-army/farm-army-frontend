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

            $sql = "INSERT INTO farm (hash, farm_id, created_at, last_found_at, updated_at, json, name, tvl, token) VALUES (:hash, :farm_id, :created_at, :last_found_at, :updated_at, :json, :name, :tvl, :token) "
                . "ON CONFLICT(farm_id) DO UPDATE SET last_found_at = :last_found_at, json = :json, updated_at = :updated_at, name = :name, tvl = :tvl, token = :token, hash = :hash";

            $stmt = $this->connection->prepare($sql);

            $stmt->bindValue('farm_id', $farm['id']);
            $stmt->bindValue('hash', md5($farm['id']));
            $stmt->bindValue('name', $farm['name'] ?? null);
            $stmt->bindValue('tvl', $farm['tvl']['usd'] ?? null);
            $stmt->bindValue('created_at', $currentDate);
            $stmt->bindValue('last_found_at', $currentDate);
            $stmt->bindValue('updated_at', $currentDate);
            $stmt->bindValue('json', json_encode($farm));
            $stmt->bindValue('token', isset($farm['extra']['transactionToken']) ? strtolower($farm['extra']['transactionToken']) : null);
            $stmt->execute();
        }

        $this->connection->commit();
    }

    public function getNewFarmsTimeline(): array
    {
        $qb = $this->createQueryBuilder('f', 'f.farmId');
        $qb->select('f.farmId', 'f.createdAt');

        $qb->orderBy('f.createdAt', 'DESC');
        $qb->setMaxResults(300);

        $result = $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 10)
            ->setResultCacheId('new-farms-v1-timeline')
            ->getArrayResult();

        return $result;
    }

    /**
     * @return string[]
     */
    public function getNewFarm(): array
    {
        $qb = $this->createQueryBuilder('f', 'f.farmId');
        $qb->select('f.farmId');

        $qb->orderBy('f.createdAt', 'DESC');
        $qb->setMaxResults(20);

        $result = $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 2)
            ->setResultCacheId('new-farms-v3')
            ->getArrayResult();

        return array_keys($result);
    }

    public function getFarmHashes(): array
    {
        $qb = $this->createQueryBuilder('f');

        $qb->select('f.hash', 'f.updatedAt');
        $qb->andWhere('f.lastFoundAt <= :now');
        $qb->andWhere('f.lastFoundAt >= :lastFoundAtWindow');

        $qb->setParameter('now', date_create()->modify('+2 hours'));
        $qb->setParameter('lastFoundAtWindow', date_create()->modify('-3 days'));

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
        $qb->setParameter('lastFoundAtWindow', date_create()->modify('-3 days'));

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
            ->setResultCacheLifetime(60 * 2)
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
     * @return string[]
     */
    public function findFarmIdsByToken(string $token): array
    {
        $qb = $this->createQueryBuilder('f', 'f.farmId');
        $qb->select('f.farmId');

        $qb->andWhere('f.token = :token');
        $qb->setParameter('token', $token);

        $qb->andWhere('f.lastFoundAt >= :lastFoundAtWindow');
        $qb->setParameter('lastFoundAtWindow', $this->findLastSyncWindow());

        $qb->orderBy('f.tvl', 'DESC');
        $qb->setMaxResults(50);

        $result = $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 2)
            ->setResultCacheId('farm-by-v1-token' . md5($token))
            ->getArrayResult();

        return array_keys($result);
    }

    public function findFarmIdByHash(string $hash): ?Farm
    {
        $qb = $this->createQueryBuilder('f', 'f.farmId');

        $qb->andWhere('f.hash = :hash');
        $qb->setParameter('hash', $hash);

        $qb->andWhere('f.lastFoundAt >= :lastFoundAtWindow');
        $qb->setParameter('lastFoundAtWindow', date_create('today')->modify('-30 day'));

        $qb->setMaxResults(1);

        $result = $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 10)
            ->setResultCacheId('farm-by-v3-hash' . md5($hash))
            ->getOneOrNullResult();

        return $result;
    }

    /**
     * @return string[]
     */
    public function getTvl(): array
    {
        $qb = $this->createQueryBuilder('f', 'f.farmId');
        $qb->select('f.farmId');

        $qb->andWhere('f.tvl > 0');

        $qb->orderBy('f.tvl', 'DESC');
        $qb->setMaxResults(10);

        $result = $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 2)
            ->setResultCacheId('tvl-farms-v3')
            ->getArrayResult();

        return array_keys($result);
    }
}
