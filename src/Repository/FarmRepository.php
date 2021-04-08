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

            $sql = "INSERT INTO farm (farm_id, created_at, last_found_at, updated_at, json, name) VALUES (:farm_id, :created_at, :last_found_at, :updated_at, :json, :name) "
                . "ON CONFLICT(farm_id) DO UPDATE SET last_found_at = :last_found_at, json = :json, updated_at = :updated_at, name = :name";

            $stmt = $this->connection->prepare($sql);

            $stmt->bindValue('farm_id', $farm['id']);
            $stmt->bindValue('name', $farm['name'] ?? null);
            $stmt->bindValue('created_at', $currentDate);
            $stmt->bindValue('last_found_at', $currentDate);
            $stmt->bindValue('updated_at', $currentDate);
            $stmt->bindValue('json', json_encode($farm));

            $stmt->execute();
        }

        $this->connection->commit();
    }

    /**
     * @return string[]
     */
    public function getNewFarm(): array
    {
        $qb = $this->createQueryBuilder('f', 'f.farmId');
        $qb->select('f.farmId');

        $qb->orderBy('f.createdAt', 'DESC');
        $qb->setMaxResults(10);

        $result = $qb->getQuery()
            ->useQueryCache(true)
            ->setResultCacheLifetime(60 * 10)
            ->setResultCacheId('new-farms')
            ->getArrayResult();

        return array_keys($result);
    }
}
