<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\CrossFarmRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CrossFarmRepository::class)
 * @ORM\Table(name="cross_farm",
 *     indexes={
 *        @ORM\Index(name="crossfarm_created_at", columns={"created_at"}),
 *        @ORM\Index(name="crossfarm_last_found_at", columns={"last_found_at"}),
 *        @ORM\Index(name="crossfarm_tvl", columns={"tvl"}),
 *        @ORM\Index(name="crossfarm_provider", columns={"provider"}),
 *        @ORM\Index(name="crossfarm_hash", columns={"hash"}),
 *        @ORM\Index(name="crossfarm_farm_id", columns={"farm_id"}),
 *        @ORM\Index(name="crossfarm_token", columns={"token"}),
 *        @ORM\Index(name="crossfarm_chain", columns={"chain"}),
 *        @ORM\Index(name="crossfarm_compound", columns={"compound"}),
 *        @ORM\Index(name="crossfarm_leverage", columns={"leverage"}),
 *        @ORM\Index(name="crossfarm_inactive", columns={"inactive"}),
 *        @ORM\Index(name="crossfarm_deprecated", columns={"deprecated"}),
 *        @ORM\Index(name="crossfarm_token_last_found_at", columns={"token", "last_found_at"}),
 *     },
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="crossfarm_farm_id", columns={"farm_id"})
 *    })
 */
class CrossFarm
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
     * @ORM\Column(type="string", nullable=true)
     */
    private $provider;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $apy;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $apyHistory;

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
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $token;

    /**
     * @ORM\Column(type="string", nullable=true, length=15)
     */
    private $chain;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $leverage = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $compound = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $inactive = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $deprecated = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $ibToken = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $stable = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $bond = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $amm = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $earns = false;

    /**
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $token0;

    /**
     * @ORM\Column(type="string", nullable=true, length=50)
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

    public function getChain(): ?string
    {
        return $this->chain;
    }
}
