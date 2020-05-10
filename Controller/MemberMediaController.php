<?php

namespace GaylordP\UserMediaBundle\Controller;

use App\Entity\User;
use App\Entity\UserMedia;
use GaylordP\UserMediaBundle\Entity\UserMediaComment;
use GaylordP\UserMediaBundle\Form\UserMediaCommentType;
use GaylordP\UserBundle\Provider\UserProvider;
use GaylordP\UserMediaBundle\Provider\UserMediaProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MemberMediaController extends AbstractController
{
    /**
     * @Route(
     *     {
     *         "fr": "/@{slug}/{_token}",
     *     },
     *     name="member_media",
     *     methods=
     *     {
     *         "GET",
     *         "POST",
     *     }
     * )
     * @Entity("member", expr="repository.findOneBySlug(slug)")
     * @Entity("userMedia", expr="repository.findOneByToken(_token)")
     * @Security("member.getId() === userMedia.getCreatedBy().getId()")
     */
    public function media(
        Request $request,
        User $member,
        UserMedia $userMedia,
        UserMediaProvider $userMediaProvider,
        UserProvider $userProvider
    ): Response {
        $entityManager = $this->getDoctrine()->getManager();

        $userMediaComment = new UserMediaComment();
        $userMediaComment->setUserMedia($userMedia);

        $form = $this->createForm(UserMediaCommentType::class, $userMediaComment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($userMediaComment);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                [
                    'user.media.' . (true == $userMedia->getMedia()->getIsImage() ? 'photo' : 'video') . '.comment.created_successfully',
                    [],
                    'user_media'
                ]
            );

            return $this->redirectToRoute('member_media', [
                'slug' => $userMedia->getCreatedBy()->getSlug(),
                'token' => $userMedia->getMedia()->getToken(),
            ]);
        }

        $userMediaComments = $entityManager
            ->getRepository(UserMediaComment::class)
            ->findByUserMedia($userMedia, [
                'id' => 'DESC'
            ])
        ;

        $userMedia->{UserMediaProvider::COUNT_COMMENT} = count($userMediaComments);

        $userMediaProvider->addExtraInfos($userMedia,
            true,
            false
        );

        $userProvider->addExtraInfos($userMedia->getCreatedBy(),
            true
        );

        return $this->render('@UserMedia/member/media.html.twig', [
            'user_media' => $userMedia,
            'user_media_comments' => $userMediaComments,
            'form' => $form->createView(),
        ]);
    }
}
