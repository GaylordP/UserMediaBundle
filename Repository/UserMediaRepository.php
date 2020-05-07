<?php

namespace GaylordP\UserMediaBundle\Repository;

use App\Entity\UserMedia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class UserMediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMedia::class);
    }

    public function findOneByUuid(string $uuid): ?UserMedia
    {
        return $this
            ->createQueryBuilder('userMedia')
            ->innerJoin('userMedia.media', 'media')
            ->andWhere('media.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->select('
                userMedia,
                media
            ')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByUuidAndName(string $uuid, string $name): ?UserMedia
    {
        return $this
            ->createQueryBuilder('userMedia')
            ->innerJoin('userMedia.media', 'media')
            ->andWhere('media.uuid = :uuid')
            ->andWhere('media.name = :name')
            ->setParameter('uuid', $uuid)
            ->setParameter('name', $name)
            ->select('
                userMedia,
                media
            ')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
