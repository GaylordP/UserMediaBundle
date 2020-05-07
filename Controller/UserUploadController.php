<?php

namespace GaylordP\UserMediaBundle\Controller;

use App\Entity\UserMedia;
use App\Form\UserMediaType;
use GaylordP\UserMediaBundle\Entity\Media;
use GaylordP\UserMediaBundle\Form\Type\UploadType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File as FileValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserUploadController extends AbstractController
{
    private $request;
    private $uploadDirectory;
    private $translator;
    private $validator;
    private $uploadConstraints;
    private $accessor;

    public function __construct(
        RequestStack $requestStack,
        ParameterBagInterface $parameters,
        TranslatorInterface $translator,
        ValidatorInterface $validator,
        PropertyAccessorInterface $accessor
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->uploadDirectory = $parameters->get('upload_directory');
        $this->translator = $translator;
        $this->validator = $validator;
        $this->accessor = $accessor;
    }

    /**
     * @Route(
     *     {
     *         "fr": "/user/upload",
     *     },
     *     name="upload",
     *     methods="POST"
     * )
     */
    public function upload(Request $request): Response
    {
        $this->uploadConstraints = json_decode($request->headers->get('upload_constraints'), true);

        $uploadedFile = $request->files->get('file');

        if (null === $uploadedFile) {
            return new JsonResponse($this->translator->trans('The file could not be uploaded.', [], 'validators'), Response::HTTP_BAD_REQUEST);
        }

        if (null === $request->get('dzchunkindex')) {
            return $this->uploadSimple($uploadedFile);
        } else {
            return $this->uploadChunk($request, $uploadedFile);
        }
    }

    private function returnSuccessResponse(string $path): JsonResponse
    {
        try {
            $entityManager = $this->getDoctrine()->getManager();

            $file = new File($this->uploadDirectory . $path);

            $userMedia = new UserMedia();
            /*
            foreach ($this->request->request->all() as $key => $value) {
                $this->accessor->setValue($userMedia, $key, $value);
            }
            */

            $form = $this->createForm(UserMediaType::class, $userMedia);
            $form->submit($this->request->request->all());

            if (false === $form->isValid()) {
                $arrayErrors = [];

                foreach ($form->all() as $children) {
                    foreach ($children->getErrors() as $error) {
                        $arrayErrors[] = $this->translator->trans($children->getConfig()->getOption('label'), [], $children->getConfig()->getOption('translation_domain')) . ' : ' . $error->getMessage();
                    }
                }

                if (!empty($arrayErrors)) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => implode(' ; ', $arrayErrors),
                        'path' => $path,
                    ]);
                } else {
                    return new JsonResponse($this->translator->trans('The file could not be uploaded.', [], 'validators'), Response::HTTP_BAD_REQUEST);
                }
            }

            $media = new Media();
            $media->setFile($file);

            $userMedia->setMedia($media);
            $entityManager->persist($userMedia);
            $entityManager->flush();

            /*
            $userMediaProvider->addExtraInfos($userMedia,
                true,
                true,
                true,
            );
            */

            $html = $this->renderView('@UserMedia/item/_item_container.html.twig', [
                'user' => $this->getUser(),
                'user_media' => $userMedia,
            ]);

            return new JsonResponse([
                'success' => true,
                'path' => str_replace('/tmp', '', $path),
                'html' => $html,
            ]);
        } catch (\Exception $e) {
            dump($e);
            return new JsonResponse($this->translator->trans('The file could not be uploaded.', [], 'validators'), Response::HTTP_BAD_REQUEST);
        }
    }

    private function uploadSimple(UploadedFile $uploadedFile): JsonResponse
    {
        $uuid = uuid_create(UUID_TYPE_RANDOM);
        $uploadFileDirectory = '/tmp/' . $uuid . '/';

        mkdir($this->uploadDirectory . $uploadFileDirectory, 0777, true);

        $fileName = $this->getFileName($uploadedFile);
        $uploadedNewFilePath = $this->uploadDirectory . $uploadFileDirectory . $fileName;

        if ('combined' === $uploadedFile->getFilename()) {
            rename($uploadedFile->getRealPath(), $uploadedNewFilePath);
        } else {
            move_uploaded_file($uploadedFile, $uploadedNewFilePath);
        }

        if (null !== $errors = $this->getErrors(new File($uploadedNewFilePath))) {
            return new JsonResponse([
                'success' => false,
                'message' => $errors,
                'path' => $uploadFileDirectory . $fileName,
            ]);
        }

        return $this->returnSuccessResponse($uploadFileDirectory . $fileName);
    }

    private function uploadChunk(Request $request, UploadedFile $uploadedFile): JsonResponse
    {
        $chunkUuid = $request->get('dzuuid');
        $chunkTotalParts = (int)$request->get('dztotalchunkcount') ?? 1;
        $chunkIndex = (int)$request->get('dzchunkindex');

        $chunkFileDirectory = '/tmp/chunk/' . $chunkUuid . '/';

        if (0 === $chunkIndex) {
            mkdir($this->uploadDirectory . $chunkFileDirectory, 0777, true);
        }

        $uploadedChunkNewFilePath = $this->uploadDirectory . $chunkFileDirectory . $chunkIndex;

        if (move_uploaded_file($uploadedFile, $uploadedChunkNewFilePath)) {
            if ($chunkIndex === ($chunkTotalParts - 1)) {
                return $this->combineChunk($uploadedFile, $this->uploadDirectory . $chunkFileDirectory, $chunkTotalParts);
            }

            return new JsonResponse([
                'chunk_success' => true,
            ]);
        }
    }

    private function combineChunk(
        UploadedFile $lastUploadedFile,
        string $chunkUploadDirectory,
        int $chunkTotalParts
    ): JsonResponse {
        $combinedChunkFilePath = $chunkUploadDirectory . 'combined';

        $target = fopen($combinedChunkFilePath, 'wb');

        for ($i = 0; $i < $chunkTotalParts; $i++) {
            $chunk = fopen($chunkUploadDirectory . $i, "rb");
            stream_copy_to_stream($chunk, $target);
            fclose($chunk);
        }

        fclose($target);

        for ($i = 0; $i < $chunkTotalParts; $i++) {
            unlink($chunkUploadDirectory . $i);
        }

        $newUploadedFile = new UploadedFile($combinedChunkFilePath, $lastUploadedFile->getClientOriginalName());

        $uploadSimple = $this->uploadSimple($newUploadedFile);

        rmdir($chunkUploadDirectory);
        return $uploadSimple;
    }

    private function getFileName(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);

        return $safeFilename . '-'.uniqid() . '.' . $file->guessExtension();
    }

    private function getErrors(File $file): ?string
    {
        unset($this->uploadConstraints['maxSizeBinary']);

        $errors = $this->validator->validate(
            $file,
            new FileValidator(UploadType::removeExtraFileConstraints($this->uploadConstraints))
        );

        if (0 === count($errors)) {
            return null;
        } else {
            $messageList = [];

            foreach ($errors as $error) {
                $messageList[] = $error->getMessage();
            }

            return implode(' ', $messageList);
        }
    }
}
