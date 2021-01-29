<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Repository\CommentsRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MediaRepository;
use App\Repository\TricksRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Twig\Environment;

/**
 * Class TricksController
 * @package App\Controller
 */
class TricksController extends AbstractController
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
     * @var CommentsRepository
     */
    private $commentsRepository;
    /**
     * @var int
     */
    private $offset = 0;
    /**
     * @var int
     */
    private $limit = 4;
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
    private $entityManager;
    /**
     * @var \App\Controller\Environment|\Twig\Environment
     */
    private $twig;
    /**
     * @var \App\Repository\UsersRepository
     */
    private $usersRepository;

    /**
     * MainController constructor.
     *
     * @param \App\Repository\TricksRepository     $tricksRepository
     * @param \App\Repository\MediaRepository      $mediaRepository
     * @param \App\Repository\CommentsRepository   $commentsRepository
     * @param \App\Repository\UsersRepository      $usersRepository
     * @param \Twig\Environment                    $twig
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(
        TricksRepository $tricksRepository,
        MediaRepository $mediaRepository,
        CommentsRepository $commentsRepository,
        UsersRepository $usersRepository,
        Environment $twig,
        EntityManagerInterface $entityManager
    ){
        $this->tricksRepository = $tricksRepository;
        $this->mediaRepository = $mediaRepository;
        $this->commentsRepository = $commentsRepository;
        $this->usersRepository = $usersRepository;
        $this->encoders = [new JsonEncoder()];
        $this->normalizers = [new JsonSerializableNormalizer()];
        $this->serializer = new Serializer($this->normalizers, $this->encoders);
        $this->twig = $twig;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/{slug}", name="tricks")
     * @param string                                    $slug
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(string $slug, Request $request): Response
    {
        $trick = $this->tricksRepository->findOneBy(['slug' => $slug]);

        if (!$trick) {
            throw $this->createAccessDeniedException('Ce trick n\'existe pas');
        }

        $date = new \DateTime('Now');
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUser($this->usersRepository->find(1));
            $comment->setCreatedAt($date);
            $comment->setTrick($trick);

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            return $this->redirectToRoute('tricks', ['slug' => $trick->getSlug()]);
        }

        return $this->render('tricks/index.html.twig', [
            'trick' => $trick, 'offset' => $this->offset, 'comment_form' => $form->createView()]);
    }

    /**
     * @Route("/{slug}/comments", name="comments")

     * @param string $slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getComments(string $slug, Request $request): Response
    {
        $this->offset = $request->query->getInt('offset');
        $comments = $this->commentsRepository->findBy(
            ['trick' => $this->tricksRepository->findOneBy(['slug' => $slug])],
            ['created_at' => 'desc'],
            $this->limit,
            $this->offset
        );

        return new JsonResponse(['comments' => $this->serializer->serialize($comments, 'json')]);
    }
}
