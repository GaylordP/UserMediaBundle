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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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
        UserProvider $userProvider,
        PublisherInterface $publisher,
        NormalizerInterface $normalizer
    ): Response {
        $entityManager = $this->getDoctrine()->getManager();

        $userMediaComment = new UserMediaComment();
        $userMediaComment->setUserMedia($userMedia);

        $form = $this->createForm(UserMediaCommentType::class, $userMediaComment, [
            'attr' => [
                'action' => $request->getRequestUri(),
                'data-form-ajax' => 'true',
            ]
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($userMediaComment);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                $userMediaProvider->addExtraInfos($userMedia);

                $update = new Update(
                    'https://bubble.lgbt/user-media/' . $userMedia->getToken() . '/comment',
                    json_encode([
                        'token' => $userMedia->getToken(),
                        'count' => $userMedia->{UserMediaProvider::COUNT_COMMENT},
                        'commentHtml' => $this->renderView('@UserMedia/member/_comment.html.twig', [
                            'comment' => $userMediaComment,
                        ])
                    ]),
                    false,
                    null,
                    'user_media_comment'
                );
                $publisher($update);

                return new JsonResponse(null, Response::HTTP_OK);
            } else {
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
                    '_token' => $userMedia->getToken(),
                ]);
            }
        }

        $userMediaComments = $entityManager
            ->getRepository(UserMediaComment::class)
            ->findByUserMedia($userMedia, [
                'id' => 'ASC'
            ])
        ;

        $userMedia->{UserMediaProvider::COUNT_COMMENT} = count($userMediaComments);

        $userMediaProvider->addExtraInfos($userMedia);

        $userProvider->addExtraInfos($userMedia->getCreatedBy());

        if ($request->isXmlHttpRequest()) {
            if ($form->isSubmitted()) {
                return new JsonResponse([
                    'action' => 'replace',
                    'target' => '.comments > form',
                    'html' => $this->renderView('@UserMedia/member/_comment_form.html.twig', [
                        'form' => $form->createView(),
                    ]),
                ], Response::HTTP_PARTIAL_CONTENT);
            } else {
                $userProvider->addExtraInfos($member);

                return new JsonResponse([
                    'action' => 'show-modal',
                    'title' => $this->renderView('@UserMedia/member/_title.html.twig', [
                        'user_media' => $userMedia,
                    ]),
                    'body' => $this->renderView('@UserMedia/member/_content.html.twig', [
                        'user_media' => $userMedia,
                        'user_media_comments' => $userMediaComments,
                        'form' => $form->createView(),
                    ])
                ], Response::HTTP_PARTIAL_CONTENT);
            }
        } else {
            return $this->render('@UserMedia/member/media.html.twig', [
                'user_media' => $userMedia,
                'user_media_comments' => $userMediaComments,
                'form' => $form->createView(),
            ]);
        }
    }
}
