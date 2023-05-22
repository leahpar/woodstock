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

    #[ORM\Column]
    public string $class;

    #[ORM\Column]
    public int $target;

    #[ORM\Column]
    public ?string $entityString = null;

    #[ORM\Column(type: 'json', nullable: true)]
    public ?array $data = null;

    public ?LoggableEntity $entity = null;

    public function __construct(
        string $type,
        User $user,
        LoggableEntity $entity,
    ) {
        $this->action = $type;
        // Pour avoir la bonne classe, au lieu du proxy en cache (Proxies\__CG__\App\Entity\*)
        $this->class = preg_replace("/.*(App\\\\Entity\\\\.*)/", "$1", get_class($entity));
        $this->user = $user;
        $this->target = $entity->id;
        $this->entityString = $entity->toLog();
        $this->date = new \DateTime();
    }

    public function getEntityType(): string
    {
        return strtolower(preg_replace("/.*\\\\(.*)$/", "$1", $this->class));
    }

}
