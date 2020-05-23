<?php

namespace GaylordP\UserMediaBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use GaylordP\UserMediaBundle\Entity\UserMedia;
use Hashids\Hashids;

class UserMediaListener
{
    private $salt;

    public function __construct(string $salt)
    {
        $this->salt = $salt;
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof UserMedia) {
            $hashids = new Hashids($this->salt, 4);
            $object->setToken($hashids->encode($object->getId()));

            $args->getEntityManager()->flush();
        }
    }
}
