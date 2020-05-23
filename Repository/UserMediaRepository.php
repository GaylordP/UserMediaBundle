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

    public function findOneByToken(string $token): ?UserMedia
    {
        return $this
            ->createQueryBuilder('userMedia')
            ->innerJoin('userMedia.media', 'media')
            ->andWhere('binary(userMedia.token) = :token')
            ->setParameter('token', $token)
            ->select('
                userMedia,
                media
            ')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
