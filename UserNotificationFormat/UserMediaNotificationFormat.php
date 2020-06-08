<?php

namespace GaylordP\UserMediaBundle\UserNotificationFormat;

use GaylordP\UserBundle\UserNotificationFormat\UserNotificationFormatInterface;
use GaylordP\UserMediaBundle\Repository\UserMediaLikeRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserMediaNotificationFormat implements UserNotificationFormatInterface
{
    private $translator;
    private $userMediaLikeRepository;

    public function __construct(
        TranslatorInterface $translator,
        UserMediaLikeRepository $userMediaLikeRepository
    ) {
        $this->translator = $translator;
        $this->userMediaLikeRepository = $userMediaLikeRepository;
    }

    public function format(array $notifications): array
    {
        $likesById = [];

        array_map(function($e) use($notifications, &$likesById) {
            if ('user_media_like' === $e->getType()) {
                $likesById[$e->getElementId()] = $e;
            }
        }, $notifications);

        if (!empty($likesById)) {
            foreach ($this->userMediaLikeRepository->findById(array_keys($likesById)) as $like) {
                $this->translator->trans('user.notification.user.media.like', [

                ], 'user_media');
                dd($like);
            }
        }

        return $notifications;
    }
}
