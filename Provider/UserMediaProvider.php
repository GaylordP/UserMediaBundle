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

    private $security;
    private $userMediaLikeRepository;
    private $userMediaCommentRepository;

    public function __construct(
        Security $security,
        UserMediaLikeRepository $userMediaLikeRepository,
        UserMediaCommentRepository $userMediaCommentRepository
    ) {
        $this->security = $security;
        $this->userMediaLikeRepository = $userMediaLikeRepository;
        $this->userMediaCommentRepository = $userMediaCommentRepository;
    }

    public function addExtraInfos(
        $userMedia,
        bool $countLikeAndisUserLiked = false,
        bool $countComment = false
    ) {
        $ids = [];
        $listEntitiesById = [];

        if ($userMedia instanceof UserMedia) {
            $listEntitiesById[$userMedia->getId()] = $userMedia;
            $ids[] = $userMedia->getId();
        } elseif (is_array($userMedia) && current($userMedia) instanceof UserMedia) {
            $ids = array_map(function($e) use(&$listEntitiesById) {
                $listEntitiesById[$e->getId()] = $e;

                return $e->getId();
            }, $userMedia);
        }

        if (!empty($ids)) {
            if (true === $countLikeAndisUserLiked) {
                $likes = $this->userMediaLikeRepository->countByUserMediaId($ids);

                foreach ($likes as $like) {
                    $listEntitiesById[$like['user_media_id']]->{self::COUNT_LIKE} = $like['count_like'];
                }

                if (null !== $this->security->getUser()) {
                    $userLikes = $this->userMediaLikeRepository->getUserLikedByUserMediaId($this->security->getUser(), $ids);

                    foreach ($userLikes as $userLike) {
                        $listEntitiesById[$userLike['user_media_id']]->{self::IS_USER_LIKED} = true;
                    }
                }
            }

            if (true === $countComment) {
                $comments = $this->userMediaCommentRepository->countByUserMediaId($ids);

                foreach ($comments as $comment) {
                    $listEntitiesById[$comment['user_media_id']]->{self::COUNT_COMMENT} = $comment['count_comment'];
                }
            }

            foreach ($listEntitiesById as $entity) {
                if (true === $countLikeAndisUserLiked) {
                    if (false === property_exists($entity, self::COUNT_LIKE)) {
                        $entity->{self::COUNT_LIKE} = 0;
                    }
                    if (false === property_exists($entity, self::IS_USER_LIKED)) {
                        $entity->{self::IS_USER_LIKED} = false;
                    }
                }

                if (true === $countComment) {
                    if (false === property_exists($entity, self::COUNT_COMMENT)) {
                        $entity->{self::COUNT_COMMENT} = 0;
                    }
                }
            }
        }

        return $userMedia;
    }
}
