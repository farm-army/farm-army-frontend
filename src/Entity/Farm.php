<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\FarmRepository;

/**
 * @ORM\Entity(repositoryClass=FarmRepository::class)
 * @ORM\Table(name="farm",
 *     indexes={
 *        @ORM\Index(name="farm_created_at", columns={"created_at"})
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
     * @ORM\Column(type="string")
     */
    private $farmId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $json;

    /**
     * @ORM\Column(type="date_immutable", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="date_immutable", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="date_immutable", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $lastFoundAt;
}
