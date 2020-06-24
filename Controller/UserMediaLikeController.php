<?php

namespace GaylordP\UserMediaBundle\Controller;

use App\Entity\UserMedia;
use GaylordP\UserMediaBundle\Entity\UserMediaLike;
use GaylordP\UserMediaBundle\Provider\UserMediaProvider;
use GaylordP\UserBundle\Entity\UserNotification;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserMediaLikeController extends AbstractController
{
    /**
     * @Route(
     *     {
     *         "fr": "/user/media/{_token}/like",
     *     },
     *     name="user_media_like",
     *     methods="GET"
     * )
     * @Entity("userMedia", expr="repository.findOneByToken(_token)")
     */
    public function like(
        Request $request,
        RouterInterface $router,
        UserMedia $userMedia,
        UserMediaProvider $userMediaProvider,
        PublisherInterface $publisher,
        TranslatorInterface $translator
    ): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $findLike = $entityManager->getRepository(UserMediaLike::class)->findOneBy([
            'createdBy' => $this->getUser(),
            'userMedia' => $userMedia,
        ]);

        if (null !== $findLike) {
            $findLike->setDeletedBy($this->getUser());
            $findLike->setDeletedAt(new \DateTime());

            $notification = $entityManager->getRepository(UserNotification::class)->findOneBy([
                'type' => 'user_media_like',
                'elementId' => $findLike->getId(),
            ]);

            $notification->setDeletedBy($findLike->getDeletedBy());
            $notification->setDeletedAt($findLike->getDeletedAt());

            $entityManager->flush();

            if (!$request->isXmlHttpRequest()) {
                $this->get('session')->getFlashBag()->add(
                    'success',
                    [
                        'user.media.unlike_successfully',
                        [],
                        'user_media'
                    ]
                );
            }
        } else {
            $userMediaLike = new UserMediaLike();
            $userMediaLike->setUserMedia($userMedia);

            $entityManager->persist($userMediaLike);
            $entityManager->flush();

            $userNotification = new UserNotification();
            $userNotification->setUser($userMedia->getCreatedBy());
            $userNotification->setType('user_media_like');
            $userNotification->setElementId($userMediaLike->getId());

            $entityManager->persist($userNotification);
            $entityManager->flush();

            if (!$request->isXmlHttpRequest()) {
                $this->get('session')->getFlashBag()->add(
                    'success',
                    [
                        'user.media.like_successfully',
                        [],
                        'user_media'
                    ]
                );
            }
        }

        $userMediaProvider->addExtraInfos($userMedia);

        $update = new Update(
            'https://bubble.lgbt/user-media/' . $userMedia->getToken() . '/like',
            json_encode([
                'token' => $userMedia->getToken(),
                'count' => $userMedia->{UserMediaProvider::COUNT_LIKE},
            ]),
            false,
            null,
            'user_media_like'
        );
        $publisher($update);

        $update = new Update(
            'https://bubble.lgbt/user/' . $this->getUser()->getSlug(),
            json_encode([
                'token' => $userMedia->getToken(),
                'isLiked' => null !== $findLike ? false : true,
                'title' => null !== $findLike ? $translator->trans('action.user.media.like', [], 'user_media') : $translator->trans('action.user.media.unlike', [], 'user_media'),
            ]),
            true,
            null,
            'user_media_like_click'
        );
        $publisher($update);

        if ($request->isXmlHttpRequest()) {

            return new JsonResponse(null, Response::HTTP_OK);
        } else {
            if (
                null !== $request->headers->get('referer')
                    &&
                'login' !== $router->match(parse_url($request->headers->get('referer'))['path'])['_route']
            ) {
                return $this->redirect($request->headers->get('referer'));
            } else {
                return $this->redirectToRoute('member_profile', [
                    'slug' => $userMedia->getCreatedBy()->getSlug(),
                ]);
            }
        }
    }
}
