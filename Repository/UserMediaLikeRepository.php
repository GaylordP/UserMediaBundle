<?php

namespace GaylordP\UserMediaBundle\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use GaylordP\UserMediaBundle\Entity\UserMediaLike;

class UserMediaLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMediaLike::class);
    }

    public function countByUserMediaId(array $userMediaIds): array
    {
        return $this
            ->createQueryBuilder('userMediaLike')
            ->andWhere('userMediaLike.userMedia IN(:userMediaIds)')
            ->setParameter('userMediaIds', $userMediaIds)
            ->select('
                IDENTITY(userMediaLike.userMedia) AS user_media_id,
                COUNT(userMediaLike) AS count_like
            ')
            ->groupBy('userMediaLike.userMedia')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getUserLikedByUserMediaId(User $user, array $userMediaIds): array
    {
        return $this
            ->createQueryBuilder('userMediaLike')
            ->andWhere('userMediaLike.createdBy IN(:user)')
            ->setParameter('user', $user)
            ->andWhere('userMediaLike.userMedia IN(:userMediaIds)')
            ->setParameter('userMediaIds', $userMediaIds)
            ->select('
                IDENTITY(userMediaLike.userMedia) AS user_media_id
            ')
            ->getQuery()
            ->getResult()
        ;
    }
}
