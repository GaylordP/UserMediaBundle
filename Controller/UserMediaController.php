<?php

namespace GaylordP\UserMediaBundle\Controller;

use App\Entity\UserMedia;
use App\Form\UserMediaType;
use GaylordP\UploadBundle\Util\IsImage;
use GaylordP\UserBundle\Entity\UserNotification;
use GaylordP\UserMediaBundle\Entity\UserMediaComment;
use GaylordP\UserMediaBundle\Entity\UserMediaLike;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserMediaController extends AbstractController
{
    /**
     * @Route(
     *     {
     *         "fr": "/user/media/{_token}/edit",
     *     },
     *     name="user_media_edit",
     *     methods=
     *     {
     *         "GET",
     *         "POST",
     *     }
     * )
     * @Entity("userMedia", expr="repository.findOneByToken(_token)")
     * @Security("user.getId() === userMedia.getCreatedBy().getId()")
     */
    public function edit(Request $request, UserMedia $userMedia): Response
    {
        $form = $this->createForm(UserMediaType::class, $userMedia);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                [
                    'user.media.updated_successfully',
                    [],
                    'user_media'
                ]
            );

            return $this->redirectToRoute($this->getParameter('user_media')['action_success_redirect_path']);
        }

        return $this->render('@UserMedia/media/edit.html.twig', [
            'form' => $form->createView(),
            'user_media' => $userMedia,
        ]);
    }

    /**
     * @Route(
     *     {
     *         "fr": "/user/media/{_token}/delete",
     *     },
     *     name="user_media_delete",
     *     methods=
     *     {
     *         "GET",
     *         "POST",
     *     }
     * )
     * @Entity("userMedia", expr="repository.findOneByToken(_token)")
     * @Security("user.getId() === userMedia.getCreatedBy().getId()")
     */
    public function delete(Request $request, UserMedia $userMedia): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->render('@UserMedia/media/delete.html.twig', [
                'user_media' => $userMedia,
            ]);
        }

        $entityManager = $this->getDoctrine()->getManager();

        $userMedia->setDeletedAt(new \DateTime());
        $userMedia->setDeletedBy($this->getUser());

        if (null !== $this->getUser()->getUserMedia() && $this->getUser()->getUserMedia() === $userMedia) {
            $this->getUser()->setUserMedia(null);
        }

        $likes = $entityManager->getRepository(UserMediaLike::class)->findByUserMedia($userMedia);

        foreach ($likes as $like) {
            $like->setDeletedAt($userMedia->getDeletedAt());
            $like->setDeletedBy($userMedia->getDeletedBy());

            $notification = $entityManager->getRepository(UserNotification::class)->findOneBy([
                'type' => 'user_media_like',
                'elementId' => $like->getId(),
            ]);

            $notification->setDeletedAt($userMedia->getDeletedAt());
            $notification->setDeletedBy($userMedia->getDeletedBy());
        }

        $comments = $entityManager->getRepository(UserMediaComment::class)->findByUserMedia($userMedia);

        foreach ($comments as $comment) {
            $comment->setDeletedAt($userMedia->getDeletedAt());
            $comment->setDeletedBy($userMedia->getDeletedBy());

            $notification = $entityManager->getRepository(UserNotification::class)->findOneBy([
                'type' => 'user_media_comment',
                'elementId' => $comment->getId(),
            ]);

            $notification->setDeletedAt($userMedia->getDeletedAt());
            $notification->setDeletedBy($userMedia->getDeletedBy());
        }

        $entityManager->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            [
                'user.media.deleted_successfully',
                [],
                'user_media'
            ]
        );

        return $this->redirectToRoute($this->getParameter('user_media')['action_success_redirect_path']);
    }

    /**
     * @Route(
     *     {
     *         "fr": "/user/media/{_token}/profile",
     *     },
     *     name="user_media_profile",
     *     methods=
     *     {
     *         "GET",
     *         "POST",
     *     }
     * )
     * @Entity("userMedia", expr="repository.findOneByToken(_token)")
     * @Security("user.getId() === userMedia.getCreatedBy().getId()")
     */
    public function profile(
        Request $request,
        UserMedia $userMedia
    ): Response {
        if (
            false === IsImage::check($userMedia->getMedia()->getMime())
                ||
            $userMedia === $this->getUser()->getUserMedia()
        ) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('profile', $request->request->get('token'))) {
            return $this->render('@UserMedia/media/profile.html.twig', [
                'user_media' => $userMedia,
            ]);
        }

        $entityManager = $this->getDoctrine()->getManager();

        $this->getUser()->setUserMedia($userMedia);

        $entityManager->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            [
                'user.media.profiled_successfully',
                [],
                'user_media'
            ]
        );

        return $this->redirectToRoute($this->getParameter('user_media')['action_success_redirect_path']);
    }

    /**
     * @Route(
     *     {
     *         "fr": "/user/media/{_token}/unprofile",
     *     },
     *     name="user_media_unprofile",
     *     methods=
     *     {
     *         "GET",
     *         "POST",
     *     }
     * )
     * @Entity("userMedia", expr="repository.findOneByToken(_token)")
     * @Security("user.getId() === userMedia.getCreatedBy().getId()")
     */
    public function unprofile(
        Request $request,
        UserMedia $userMedia
    ): Response {
        if (
            false === IsImage::check($userMedia->getMedia()->getMime())
                ||
            $userMedia !== $this->getUser()->getUserMedia()
        ) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('unprofile', $request->request->get('token'))) {
            return $this->render('@UserMedia/media/unprofile.html.twig', [
                'user_media' => $userMedia,
            ]);
        }

        $entityManager = $this->getDoctrine()->getManager();

        $this->getUser()->setUserMedia(null);

        $entityManager->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            [
                'user.media.unprofiled_successfully',
                [],
                'user_media'
            ]
        );

        return $this->redirectToRoute($this->getParameter('user_media')['action_success_redirect_path']);
    }
}
