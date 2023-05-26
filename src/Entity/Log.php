<?php

namespace App\Entity;

use App\Logger\LoggableEntity;
use App\Repository\LogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LogRepository::class)]
class Log
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    public \DateTime $date;

    #[ORM\Column]
    public string $action;

    #[ORM\ManyToOne(targetEntity: User::class)]
    public User $user;

    #[ORM\Column(nullable: true)]
    public ?string $class;

    #[ORM\Column(nullable: true)]
    public ?int $target;

    #[ORM\Column(nullable: true)]
    public ?string $entityString = null;

    #[ORM\Column(type: 'json', nullable: true)]
    public ?array $data = null;

    // Pour affichage
    public ?LoggableEntity $entity = null;

    public function __construct(
        string $type,
        User $user,
        ?LoggableEntity $entity,
    ) {
        $this->action = $type;

        if ($entity) {
            // Pour avoir la bonne classe, au lieu du proxy en cache (Proxies\__CG__\App\Entity\*)
            $this->class = preg_replace("/.*(App\\\\Entity\\\\.*)/", "$1", get_class($entity));
            $this->target = $entity->id;
            $this->entityString = $entity->toLog();
        }

        $this->user = $user;
        $this->date = new \DateTime();
    }

    public function getEntityType(): ?string
    {
        return $this->class ? strtolower(preg_replace("/.*\\\\(.*)$/", "$1", $this->class)) : null;
    }

}
