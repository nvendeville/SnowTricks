<?php

namespace App\Controller;

use App\Repository\MediaRepository;
use App\Repository\TricksRepository;
use App\Service\HasMore;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


/**
 * Class MainController
 * @package App\Controller
 */
class MainController extends AbstractController
{
    use HasMore;

    /**
     * @var TricksRepository
     */
    private TricksRepository $tricksRepository;
    /**
     * @var MediaRepository
     */
    private MediaRepository $mediaRepository;
    /**
     * @var int
     */
    private $offset = 0;
    /**
     * @var int
     */
    private $limit = 5;
    /**
     * @var \Symfony\Component\Serializer\Encoder\JsonEncoder[]
     */
    private array $encoders;
    /**
     * @var \Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer[]
     */
    private array $normalizers;
    /**
     * @var \Symfony\Component\Serializer\Serializer
     */
    private Serializer $serializer;


    /**
     * MainController constructor.
     *
     * @param \App\Repository\TricksRepository $tricksRepository
     * @param \App\Repository\MediaRepository  $mediaRepository
     */
    public function __construct(TricksRepository $tricksRepository, MediaRepository $mediaRepository)
    {
        $this->tricksRepository = $tricksRepository;
        $this->mediaRepository = $mediaRepository;
        $this->encoders = [new JsonEncoder()];
        $this->normalizers = [new JsonSerializableNormalizer()];
        $this->serializer = new Serializer($this->normalizers, $this->encoders);
    }

    /**
     * @Route("/", name="accueil")
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request): Response
    {
        $isAjax = $request->isXMLHttpRequest();

        if ($isAjax) {
            $this->offset = $request->query->getInt('offset');
        }

        $tricks = $this->tricksRepository->findBy([], ['created_at' => 'desc'], $this->limit, $this->offset);
        $hasMore = $this->hasMore($this->tricksRepository, count($tricks), $this->limit, $this->offset + $this->limit);

        if ($isAjax) {
            $response = [
                'tricks' => $this->render('trick/_listTricks.html.twig', ['tricks' => $tricks])->getContent(),
                'hasMore' => $hasMore
            ];
            return new JsonResponse($response);
        }

        return $this->render('main/index.html.twig', [
            'tricks' => $tricks,
            'offset' => ($this->offset + $this->limit),
            'hasMore' => $hasMore
        ]);
    }
}
