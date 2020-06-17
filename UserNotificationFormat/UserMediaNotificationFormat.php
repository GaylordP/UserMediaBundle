<?php

namespace GaylordP\UserMediaBundle\UserNotificationFormat;

use GaylordP\UserBundle\UserNotificationFormat\UserNotificationFormatInterface;
use GaylordP\UserMediaBundle\Repository\UserMediaCommentRepository;
use GaylordP\UserMediaBundle\Repository\UserMediaLikeRepository;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class UserMediaNotificationFormat implements UserNotificationFormatInterface
{
    private $twig;
    private $router;
    private $userMediaCommentRepository;
    private $userMediaLikeRepository;

    public function __construct(
        Environment $twig,
        RouterInterface $router,
        UserMediaCommentRepository $userMediaCommentRepository,
        UserMediaLikeRepository $userMediaLikeRepository
    ) {
        $this->twig = $twig;
        $this->router = $router;
        $this->userMediaCommentRepository = $userMediaCommentRepository;
        $this->userMediaLikeRepository = $userMediaLikeRepository;
    }

    public function format(array $notifications): array
    {
        $this->user_media_comment($notifications);
        $this->user_media_like($notifications);

        return $notifications;
    }

    private function user_media_comment(array $notifications): void
    {
        $notificationsByCommentId = [];

        array_map(function($e) use(&$notificationsByCommentId) {
            if ('user_media_comment' === $e->getType()) {
                $notificationsByCommentId[$e->getElementId()] = $e;
            }
        }, $notifications);

        if (!empty($notificationsByCommentId)) {
            foreach ($this->userMediaCommentRepository->findById(array_keys($notificationsByCommentId)) as $comment) {
                $notificationsByCommentId[$comment->getId()]->__text = $this
                    ->twig
                    ->render('@UserMedia/notification/_user_media_comment.html.twig', [
                        'comment' => $comment,
                    ])
                ;

                $notificationsByCommentId[$comment->getId()]->__color = $comment
                    ->getCreatedBy()
                    ->getColor()
                    ->getSlug()
                ;

                $notificationsByCommentId[$comment->getId()]->__link = $this
                    ->router
                    ->generate('member_media', [
                        'slug' => $comment->getUserMedia()->getCreatedBy()->getSlug(),
                        '_token' => $comment->getUserMedia()->getToken(),
                    ])
                ;
            }
        }
    }

    private function user_media_like(array $notifications): void
    {
        $notificationsByLikeId = [];

        array_map(function($e) use(&$notificationsByLikeId) {
            if ('user_media_like' === $e->getType()) {
                $notificationsByLikeId[$e->getElementId()] = $e;
            }
        }, $notifications);

        if (!empty($notificationsByLikeId)) {
            foreach ($this->userMediaLikeRepository->findById(array_keys($notificationsByLikeId)) as $like) {
                $notificationsByLikeId[$like->getId()]->__color = $like
                    ->getCreatedBy()
                    ->getColor()
                    ->getSlug()
                ;

                $notificationsByLikeId[$like->getId()]->__link = $this
                    ->router
                    ->generate('member_media', [
                        'slug' => $like->getUserMedia()->getCreatedBy()->getSlug(),
                        '_token' => $like->getUserMedia()->getToken(),
                    ])
                ;

                $notificationsByLikeId[$like->getId()]->__text = $this
                    ->twig
                    ->render('@UserMedia/notification/_user_media_like.html.twig', [
                        'like' => $like,
                    ])
                ;
            }
        }
    }
}
