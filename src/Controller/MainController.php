<?php

namespace App\Controller;

use App\Repository\MediaRepository;
use App\Repository\TricksRepository;
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
    /**
     * @var TricksRepository
     */
    private $tricksRepository;
    /**
     * @var MediaRepository
     */
    private $mediaRepository;
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
    private $encoders;
    /**
     * @var \Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer[]
     */
    private $normalizers;
    /**
     * @var \Symfony\Component\Serializer\Serializer
     */
    private $serializer;

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


        if ($isAjax) {
            return new JsonResponse(['tricks' => $this->serializer->serialize($tricks, 'json')]);
            //return new JsonResponse(['tricks' => $tricks]);

        }

        return $this->render('main/index.html.twig', ['tricks' => $tricks, 'offset' => ($this->offset + 5)]);
    }
}
