<?php

namespace GaylordP\UserMediaBundle\Provider;

use App\Entity\UserMedia;
use GaylordP\UserMediaBundle\Repository\UserMediaCommentRepository;
use GaylordP\UserMediaBundle\Repository\UserMediaLikeRepository;
use Symfony\Component\Security\Core\Security;

class UserMediaProvider
{
    const COUNT_COMMENT = '__countComment';
    const COUNT_LIKE = '__countLike';
    const IS_USER_LIKED = '__isUserLiked';

    protected $security;
    protected $userMediaLikeRepository;
    protected $userMediaCommentRepository;

    public function __construct(
        Security $security,
        UserMediaLikeRepository $userMediaLikeRepository,
        UserMediaCommentRepository $userMediaCommentRepository
    ) {
        $this->security = $security;
        $this->userMediaLikeRepository = $userMediaLikeRepository;
        $this->userMediaCommentRepository = $userMediaCommentRepository;
    }

    public function addExtraInfos($userMedia)
    {
        $listEntitiesById = [];

        if ($userMedia instanceof UserMedia) {
            $listEntitiesById[$userMedia->getId()] = $userMedia;
        } elseif (is_array($userMedia) && current($userMedia) instanceof UserMedia) {
            foreach ($userMedia as $e) {
                $listEntitiesById[$e->getId()] = $e;
            }
        }

        if (!empty($listEntitiesById)) {
            /*
             * Like
             */
            $likesIds = [];
            foreach ($listEntitiesById as $e) {
                if (false === property_exists($e, self::COUNT_LIKE)) {
                    $likesIds[] = $e->getId();
                }
            }

            if (!empty($likesIds)) {
                $likes = $this->userMediaLikeRepository->countByUserMediaId($likesIds);

                foreach ($likes as $like) {
                    $listEntitiesById[$like['user_media_id']]->{self::COUNT_LIKE} = $like['count_like'];
                }
            }

            /*
             * User like
             */
            if (null !== $this->security->getUser()) {
                $userLikesIds = [];
                foreach ($listEntitiesById as $e) {
                    if (false === property_exists($e, self::IS_USER_LIKED)) {
                        $userLikesIds[] = $e->getId();
                    }
                }

                if (!empty($userLikesIds)) {
                    $userLikes = $this->userMediaLikeRepository->getUserLikedByUserMediaId($this->security->getUser(), $userLikesIds);

                    foreach ($userLikes as $userLike) {
                        $listEntitiesById[$userLike['user_media_id']]->{self::IS_USER_LIKED} = true;
                    }
                }
            }

            /*
             * Comments
             */
            $commentsIds = [];
            foreach ($listEntitiesById as $e) {
                if (false === property_exists($e, self::COUNT_COMMENT)) {
                    $commentsIds[] = $e->getId();
                }
            }

            if (!empty($commentsIds)) {
                $comments = $this->userMediaCommentRepository->countByUserMediaId($commentsIds);

                foreach ($comments as $comment) {
                    $listEntitiesById[$comment['user_media_id']]->{self::COUNT_COMMENT} = $comment['count_comment'];
                }
            }

            /*
             * Default
             */
            foreach ($listEntitiesById as $entity) {
                if (false === property_exists($entity, self::COUNT_LIKE)) {
                    $entity->{self::COUNT_LIKE} = 0;
                }

                if (false === property_exists($entity, self::IS_USER_LIKED)) {
                    $entity->{self::IS_USER_LIKED} = false;
                }

                if (false === property_exists($entity, self::COUNT_COMMENT)) {
                    $entity->{self::COUNT_COMMENT} = 0;
                }
            }
        }

        return $userMedia;
    }
}
