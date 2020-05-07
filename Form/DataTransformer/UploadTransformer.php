<?php

namespace GaylordP\UserMediaBundle\Form\DataTransformer;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\File;

class UploadTransformer implements DataTransformerInterface
{
    private $uploadDirectory;

    public function __construct(ParameterBagInterface $parameters)
    {
        $this->uploadDirectory = $parameters->get('upload_directory');
    }

    public function transform($data): ?string
    {
        return $data;
    }

    public function reverseTransform($data): array
    {
        $result = [];

        if (null !== $data) {
            $json = json_decode($data);

            foreach ($json as $path) {
                $result[] = new File($this->uploadDirectory . $path);
            }
        }

        return $result;
    }
}
