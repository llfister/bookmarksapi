<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Bookmark;
use App\Entity\Keyword;
use App\Formatter\BookmarkFormatter;
use App\Repository\BookmarkRepository;
use App\Repository\KeywordRepository;
use App\Service\BookmarkService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookmarkController extends AbstractController
{
    private SerializerInterface $serializer;
    private EntityManagerInterface $entityManager;
    private BookmarkRepository $bookmarkRepository;
    private KeywordRepository $keywordRepository;
    private ValidatorInterface $validator;
    private BookmarkFormatter $bookmarkFormatter;
    private BookmarkService $bookmarkService;

    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        BookmarkRepository $bookmarkRepository,
        KeywordRepository $keywordRepository,
        ValidatorInterface $validator,
        BookmarkFormatter $bookmarkFormatter,
        BookmarkService $bookmarkService
    ) {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->bookmarkRepository = $bookmarkRepository;
        $this->keywordRepository = $keywordRepository;
        $this->validator = $validator;
        $this->bookmarkFormatter = $bookmarkFormatter;
        $this->bookmarkService = $bookmarkService;
    }

    /**
     * @Route("/bookmarks", methods={"GET"}, name="bookmarks_list")
     */
    public function index(): JsonResponse
    {
        $bookmarks = $this->bookmarkRepository->findAll();
        $data = $this->serializer->serialize($bookmarks, 'json', ['groups' => 'get_bookmark']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/bookmarks", methods={"POST"}, name="bookmarks_create")
     */
    public function create(Request $request): JsonResponse
    {
        $content = json_decode((string) $request->getContent());
        if (!property_exists($content, 'url')) {
            throw new BadRequestHttpException('URL is required');
        }
        /**
         * @var Bookmark $bookmark
         */
        $bookmark = $this->serializer->deserialize($request->getContent(), Bookmark::class, 'json');

        $url = $bookmark->getUrl();
        if (!$this->bookmarkService->checkValidityURL($url)) {
            throw new BadRequestHttpException('This URL is not valid');
        }

        $bookmark = $this->bookmarkFormatter->getOEmbedData($url, $bookmark);

        $errors = $this->validator->validate($bookmark);
        if (\count($errors)) {
            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST, [], true);
        }

        $this->entityManager->persist($bookmark);

        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode(), [], true);
        }

        $data = $this->serializer->serialize($bookmark, 'json', ['groups' => 'get_bookmark']);

        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    /**
     * @Route("/bookmarks/{id}", methods={"GET"}, name="bookmarks_read")
     */
    public function read(Bookmark $bookmark): JsonResponse
    {
        $data = $this->serializer->serialize($bookmark, 'json', ['groups' => 'get_bookmark']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/bookmarks/{id}", methods={"PUT"}, name="bookmarks_update")
     */
    public function update(Request $request, Bookmark $bookmark): JsonResponse
    {
        $content = json_decode((string) $request->getContent());
        if (!property_exists($content, 'url')) {
            $url = $bookmark->getUrl();
        } else {
            $url = $content->url;
        }

        if (null === $url) {
            throw new BadRequestHttpException('URL is required');
        }
        if (!$this->bookmarkService->checkValidityURL($url)) {
            throw new BadRequestHttpException("URL $url doesn't exists");
        }
        if (property_exists($content, 'title')) {
            $bookmark->setTitle($content->title);
        }

        if ($url !== $bookmark->getUrl()) {
            $bookmark = $this->bookmarkFormatter->getOEmbedData($url, $bookmark);
        }

        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode(), [], true);
        }

        $errors = $this->validator->validate($bookmark);
        if (\count($errors)) {
            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST, [], true);
        }
        $data = $this->serializer->serialize($bookmark, 'json', ['groups' => 'get_bookmark']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/bookmarks/{id}/keywords", methods={"POST"}, name="bookmarks_keywords_add")
     * @ParamConverter("bookmark", class=Bookmark::class)
     */
    public function addKeyword(Request $request, Bookmark $bookmark): JsonResponse
    {
        $contents = json_decode((string) $request->getContent());
        foreach ($contents as $content) {
            $name = $content->name;
            if ($keyword = $this->keywordRepository->findOneByName($name)) {
                $bookmark->addKeyword($keyword);
            } else {
                $keyword = new Keyword();
                $keyword->setName($name);
                $this->entityManager->persist($keyword);
                $bookmark->addKeyword($keyword);
            }
        }

        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode(), [], true);
        }

        $data = $this->serializer->serialize($bookmark, 'json', ['groups' => 'get_bookmark']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/bookmarks/{id}", methods={"DELETE"}, name="bookmarks_delete")
     */
    public function delete(Bookmark $bookmark): JsonResponse
    {
        $this->entityManager->remove($bookmark);
        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode(), [], true);
        }

        $data = $this->serializer->serialize($bookmark, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/bookmarks/{idB}/keywords/{idK}", methods={"DELETE"}, name="bookmarks_keywords_delete")
     * @ParamConverter("bookmark", class=Bookmark::class, options={"mapping"={"idB"="id"}})
     * @ParamConverter("keyword", class=Keyword::class, options={"mapping"={"idK"="id"}})
     */
    public function deleteKeyword(Bookmark $bookmark, Keyword $keyword): JsonResponse
    {
        $bookmark->removeKeyword($keyword);
        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), $e->getCode(), [], true);
        }

        $data = $this->serializer->serialize($bookmark, 'json', ['groups' => 'get_bookmark']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
