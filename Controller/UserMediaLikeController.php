<?php

namespace GaylordP\UserMediaBundle\Controller;

use App\Entity\UserMedia;
use GaylordP\UserMediaBundle\Entity\UserMediaLike;
use GaylordP\UserMediaBundle\Provider\UserMediaProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

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
        UserMediaProvider $userMediaProvider
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

        if ($request->isXmlHttpRequest()) {
            $userMediaProvider->addExtraInfos($userMedia, true, true);

            return new JsonResponse([
                'action' => 'replace',
                'target' => '#user-media-like-' . $userMedia->getMedia()->getToken(),
                'html' => $this->renderView('@UserMedia/media/item/control/_like.html.twig', [
                    'user_media' => $userMedia,
                ])
            ], Response::HTTP_PARTIAL_CONTENT);
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
