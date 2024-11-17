<?php

namespace Ambta\DoctrineEncryptBundle\Tests\Functional\fixtures\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 *
 */
#[ORM\Entity]
class DateTimeJsonArrayTarget
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    #[ORM\Id]
    #[ORM\Column(type:"integer")]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * @Ambta\DoctrineEncryptBundle\Configuration\Encrypted(type="datetime")
     * @ORM\Column(type="string", nullable=true)
     */
    #[Encrypted(type:'datetime')]
    #[ORM\Column(type:"string", nullable: true)]
    private $date;

    /**
     * @Ambta\DoctrineEncryptBundle\Configuration\Encrypted(type="json")
     * @ORM\Column(type="string", nullable=true)
     */
    #[Encrypted(type:'json')]
    #[ORM\Column(type:"string", nullable: true)]
    private $json;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date): void
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * @param mixed $json
     */
    public function setJson($json): void
    {
        $this->json = $json;
    }
}
