<?php

namespace App\Controller;

use App\Repository\CommentsRepository;
use App\Repository\TricksRepository;
use App\Service\HasMore;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/trick")
 */
class CommentController extends AbstractController
{
    use HasMore;

    private CommentsRepository $commentsRepository;
    private TricksRepository $tricksRepository;
    private int $limit = 4;
    private Serializer $serializer;
    /**
     * @var \Symfony\Component\Serializer\Encoder\JsonEncoder[]
     */
    private array $encoders;
    /**
     * @var \Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer[]
     */
    private array $normalizers;

    /**
     * CommentController constructor.
     *
     * @param \App\Repository\TricksRepository   $tricksRepository
     * @param \App\Repository\CommentsRepository $commentsRepository
     */
    public function __construct(
        TricksRepository $tricksRepository,
        CommentsRepository $commentsRepository
    ) {
        $this->tricksRepository = $tricksRepository;
        $this->commentsRepository = $commentsRepository;
        $this->encoders = [new JsonEncoder()];
        $this->normalizers = [new JsonSerializableNormalizer()];
        $this->serializer = new Serializer($this->normalizers, $this->encoders);
    }

    /**
     * @Route("/{slug}/comments", name="comments")
     * @param string                                    $slug
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getComments(string $slug, Request $request): Response
    {

        $offset = $request->query->getInt('offset');

        $comments = $this->commentsRepository->findBy(
            ['trick' => $this->tricksRepository->findOneBy(['slug' => $slug])],
            ['created_at' => 'desc'],
            $this->limit,
            $offset
        );

        $hasMore = $this->hasMore(
            $this->commentsRepository,
            count($comments),
            $this->limit,
            $offset + $this->limit
        );

        return new JsonResponse([
            'comments' => $this->serializer->serialize($comments, 'json'),
            'hasMore' => $hasMore
        ]);
    }
}
