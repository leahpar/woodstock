<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait UserInfosTrait
{
    #[ORM\Column(nullable: true)]
    public ?string $telephone = null;

    #[ORM\Column(nullable: true)]
    public ?string $taille = null;

    #[ORM\Column(nullable: true)]
    public ?string $pointure = null;

    #[ORM\Column(nullable: true)]
    public ?string $permis = null;

    #[ORM\Column(nullable: true)]
    public ?string $personneAContacter = null;
}
