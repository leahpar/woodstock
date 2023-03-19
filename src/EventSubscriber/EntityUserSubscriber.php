<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EntityUserSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}


    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function preUpdate(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();
        if ($entity instanceof User && $entity->plainPassword) {
            $this->encodePassword($entity, $entity->plainPassword);
        }
    }

    public function prePersist(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();
        if ($entity instanceof User) {
            $this->encodePassword($entity, $entity->plainPassword ?? bin2hex(random_bytes(4)));
        }
    }

    private function encodePassword(User $user, #[\SensitiveParameter] string $plainPassword): void
    {
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
    }

}
