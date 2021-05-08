<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\FarmRepository;

/**
 * @ORM\Entity(repositoryClass=FarmRepository::class)
 * @ORM\Table(name="farm",
 *     indexes={
 *        @ORM\Index(name="farm_created_at", columns={"created_at"}),
 *        @ORM\Index(name="farm_last_found_at", columns={"last_found_at"}),
 *        @ORM\Index(name="farm_tvl", columns={"tvl"}),
 *        @ORM\Index(name="farm_hash", columns={"hash"}),
 *        @ORM\Index(name="farm_farm_id", columns={"farm_id"}),
 *        @ORM\Index(name="farm_token", columns={"token"}),
 *        @ORM\Index(name="farm_token_last_found_at", columns={"token", "last_found_at"}),
 *     },
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="farm_farm_id", columns={"farm_id"})
 *    })
 */
class Farm
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $hash;

    /**
     * @ORM\Column(type="string")
     */
    private $farmId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $tvl;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $json;

    /**
     * @ORM\Column(type="datetime_immutable", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime_immutable", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $lastFoundAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $token0;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $token1;

    public function getJson(): array
    {
        return $this->json;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getFarmId(): string
    {
        return $this->farmId;
    }

}
