<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Keyword;
use App\Repository\KeywordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class KeywordController extends AbstractController
{
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * @Route("/keywords/{id}", methods={"PUT"}, name="keyword_update")
     */
    public function update(Request $request, Keyword $keyword, KeywordRepository $keywordRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $content = json_decode((string) $request->getContent());
        $name = $content->name;
        if ($keywordRepository->findOneByName($name)) {
            throw new \Exception('Keyword\'s name already used');
        }
        $keyword->setName($name);

        try {
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode(), [], true);
        }

        $errors = $this->validator->validate($keyword);
        if (\count($errors)) {
            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST, [], true);
        }

        $data = $this->serializer->serialize($keyword, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['bookmarks']]);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
