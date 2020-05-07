<?php

namespace GaylordP\UserMediaBundle\Controller;

use App\Entity\UserMedia;
use App\Entity\UserMediaLike;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class UserMediaLikeController extends AbstractController
{
    /**
     * @Route(
     *     {
     *         "fr": "/user/media/{uuid}/like",
     *     },
     *     name="user_media_like",
     *     methods="GET"
     * )
     * @Entity("userMedia", expr="repository.findOneByUuid(uuid)")
     */
    public function like(
        Request $request,
        RouterInterface $router,
        UserMedia $userMedia
    ): Response {
        $entityManager = $this->getDoctrine()->getManager();

        $findLike = $entityManager->getRepository(UserMediaLike::class)->findOneBy([
            'createdBy' => $this->getUser(),
            'userMedia' => $userMedia,
        ]);

        if (null !== $findLike) {
            $this->get('session')->getFlashBag()->add(
                'danger',
                [
                    'user.media.like_already',
                ]
            );

            return $this->redirectAfterAction($request, $router, $userMedia);
        } else {
            $userMediaLike = new UserMediaLike();
            $userMediaLike->setUserMedia($userMedia);

            $entityManager->persist($userMediaLike);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                [
                    'user.media.like_successfully',
                ]
            );

            return $this->redirectAfterAction($request, $router, $userMedia);
        }
    }

    /**
     * @Route(
     *     {
     *         "fr": "/user/media/{uuid}/unlike",
     *     },
     *     name="user_media_unlike",
     *     methods="GET"
     * )
     * @Entity("userMedia", expr="repository.findOneByUuid(uuid)")
     */
    public function unlike(
        Request $request,
        RouterInterface $router,
        UserMedia $userMedia
    ): Response {
        $entityManager = $this->getDoctrine()->getManager();

        $findLike = $entityManager->getRepository(UserMediaLike::class)->findOneBy([
            'createdBy' => $this->getUser(),
            'userMedia' => $userMedia,
        ]);

        if (null === $findLike) {
            $this->get('session')->getFlashBag()->add(
                'danger',
                [
                    'user.media.unlike_already',
                ]
            );

            return $this->redirectAfterAction($request, $router, $userMedia);
        } else {
            $findLike->setDeletedBy($this->getUser());
            $findLike->setDeletedAt(new \DateTime());

            $entityManager->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                [
                    'user.media.unlike_successfully',
                ]
            );

            return $this->redirectAfterAction($request, $router, $userMedia);
        }
    }

    private function redirectAfterAction(
        Request $request,
        RouterInterface $router,
        UserMedia $userMedia
    ): Response {
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
