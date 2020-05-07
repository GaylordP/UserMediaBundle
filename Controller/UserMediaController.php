<?php

namespace GaylordP\UserMediaBundle\Controller;

use App\Entity\UserMedia;
use App\Form\UserMediaType;
use GaylordP\UserMediaBundle\Util\IsImage;
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
     *         "fr": "/user/media/{uuid}/edit",
     *     },
     *     name="user_media_edit",
     *     methods=
     *     {
     *         "GET",
     *         "POST",
     *     }
     * )
     * @Entity("userMedia", expr="repository.findOneByUuid(uuid)")
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
     *         "fr": "/user/media/{uuid}/delete",
     *     },
     *     name="user_media_delete",
     *     methods=
     *     {
     *         "GET",
     *         "POST",
     *     }
     * )
     * @Entity("userMedia", expr="repository.findOneByUuid(uuid)")
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
     *         "fr": "/user/media/{uuid}/profile",
     *     },
     *     name="user_media_profile",
     *     methods=
     *     {
     *         "GET",
     *         "POST",
     *     }
     * )
     * @Entity("userMedia", expr="repository.findOneByUuid(uuid)")
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
     *         "fr": "/user/media/{uuid}/unprofile",
     *     },
     *     name="user_media_unprofile",
     *     methods=
     *     {
     *         "GET",
     *         "POST",
     *     }
     * )
     * @Entity("userMedia", expr="repository.findOneByUuid(uuid)")
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
