<?php

namespace GaylordP\UserMediaBundle\Provider;

use App\Entity\UserMedia;
use GaylordP\UserMediaBundle\Repository\UserMediaLikeRepository;
use Symfony\Component\Security\Core\Security;

class UserMediaProvider
{
    const COUNT_LIKE = '__countLike';
    const IS_USER_LIKED = '__isUserLiked';

    private $user;
    private $userMediaLikeRepository;

    public function __construct(
        Security $security,
        UserMediaLikeRepository $userMediaLikeRepository
    ) {
        $this->user = $security->getUser();
        $this->userMediaLikeRepository = $userMediaLikeRepository;
    }

    public function addExtraInfos(
        $userMedia,
        bool $countLikeAndisUserLiked = false
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

                if (null !== $this->user) {
                    $userLikes = $this->userMediaLikeRepository->getUserLikedByUserMediaId($this->user, $ids);

                    foreach ($userLikes as $userLike) {
                        $listEntitiesById[$userLike['user_media_id']]->{self::IS_USER_LIKED} = true;
                    }
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
            }
        }

        return $userMedia;
    }
}
