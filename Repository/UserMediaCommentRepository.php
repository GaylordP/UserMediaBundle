<?php

namespace GaylordP\UserMediaBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use GaylordP\UserMediaBundle\Entity\UserMediaComment;

class UserMediaCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMediaComment::class);
    }

    public function countByUserMediaId(array $userMediaIds): array
    {
        return $this
            ->createQueryBuilder('userMediaComment')
            ->andWhere('userMediaComment.userMedia IN(:userMediaIds)')
            ->setParameter('userMediaIds', $userMediaIds)
            ->select('
                IDENTITY(userMediaComment.userMedia) AS user_media_id,
                COUNT(userMediaComment) AS count_comment
            ')
            ->groupBy('userMediaComment.userMedia')
            ->getQuery()
            ->getResult()
        ;
    }
}
