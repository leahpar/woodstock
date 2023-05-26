<?php

namespace App\Logger;

use App\Entity\Chantier;
use App\Entity\Log;
use App\Entity\Materiel;
use App\Entity\Panier;
use App\Entity\Reference;
use App\Entity\Stock;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Serializer\SerializerInterface;

class LoggerSubscriber implements EventSubscriberInterface
{

    private array $removals = [];

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly LoggerService $logger,
    ) {}

    public function getSubscribedEvents()
    {
        return [
            //'postPersist',
            //'postUpdate',
            //'preRemove',
            //'postRemove',
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();
        $this->log($entity, 'insert', $entityManager);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();
        $this->log($entity, 'update', $entityManager);
    }

    // We need to store the entity in a temporary array here, because the entity's ID is no longer
    // available in the postRemove event. We convert it to an array here, so we can retain the ID for
    // our audit log.
    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->removals[] = $this->serializer->normalize($entity);
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();
        $this->log($entity, 'delete', $entityManager);
    }

    // This is the function which calls the AuditLogger service, constructing
    // the call to `AuditLogger::log()` with the appropriate parameters.
    private function log($entity, string $action, EntityManagerInterface $em): void
    {
        $entityClass = get_class($entity);

        if (!in_array($entityClass, [
            Materiel::class,
            Reference::class,
            Chantier::class,
            User::class,
            Panier::class
        ])) {
            return;
        }

        $entityId = method_exists($entity, 'getId') ? $entity->getId() : ($entity->id??null);
        if (!$entityId) {
            // If the entity doesn't have an ID, we can't log it.
            return;
        }

        // The Doctrine unit of work keeps track of all changes made to entities.
        $uow = $em->getUnitOfWork();

        if ($action === 'delete') {
            // For deletions, we get our entity from the temporary array.
            $entityData = array_pop($this->removals);
            $entityId = $entityData['id'];
        }
        elseif ($action === 'insert') {
            // For insertions, we convert the entity to an array.
            $entityData = $this->serializer->normalize($entity);
        }
        else {
            // For updates, we get the change set from Doctrine's Unit of Work manager.
            // This gives an array which contains only the fields which have
            // changed. We then just convert the numerical indexes to something
            // a bit more readable; "from" and "to" keys for the old and new values.
            $entityData = $uow->getEntityChangeSet($entity);
            foreach ($entityData as $field => $change) {
                $entityData[$field] = [
                    'from' => $change[0],
                    'to' => $change[1],
                ];
            }
        }
        $entityType = str_replace('App\Entity\\', '', $entityClass);
        $this->logger->log(/*$entity,*/ $entityType, $entityId, $action, $entityData);
    }

}
