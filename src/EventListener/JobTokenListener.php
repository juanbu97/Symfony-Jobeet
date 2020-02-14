<?php

namespace App\EventListener;

use App\Entity\Job;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * Servicio que crea un token automáticamente al crear un trabajo
 */
class JobTokenListener
{
    /***@param LifecycleEventArgs $args*/
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity= $args->getEntity();
        if (!$entity instanceof Job) {
            return;
        }
        if (!$entity->getToken()) {
            $entity->setToken(\bin2hex(\random_bytes(10)));
        }
    }
}
