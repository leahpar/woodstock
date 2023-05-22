<?php

namespace App\Logger;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

abstract class LoggableEntity
{

    public ?int $id = null;

    //#[ORM\Column(type: 'datetime', nullable: true)]
    //public ?\DateTime $createdAt = null;

    //#[ORM\Column(type: 'datetime', nullable: true)]
    //public ?\DateTime $updatedAt = null;

    //#[ORM\ManyToOne(targetEntity: User::class)]
    //public ?User $createdBy = null;

    //#[ORM\ManyToOne(targetEntity: User::class)]
    //public ?User $updatedBy = null;

    public function toLog()
    {
        return $this->__toString();
    }

}
