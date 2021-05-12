<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentFormType;
use App\Form\TrickFormType;
use App\Repository\CommentsRepository;
use App\Repository\MediaRepository;
use App\Repository\TricksRepository;
use App\Repository\UsersRepository;
use App\Service\HasMore;
use App\Service\SetImage;
use App\Service\SetVideo;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Twig\Environment;

/**
 * @Route("/trick")
 */
class CrudTrickController extends AbstractController
{
    use HasMore;

    use SetImage;

    use SetVideo;

    private TricksRepository $tricksRepository;

    private MediaRepository $mediaRepository;

    private CommentsRepository $commentsRepository;

    private EntityManagerInterface $entityManager;

    private Environment $twig;

    private UsersRepository $usersRepository;

    private Security $security;

    private int $offset = 0;

    /**
     * @var \Symfony\Component\Serializer\Encoder\JsonEncoder[]
     */
    private array $encoders;
    /**
     * @var \Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer[]
     */
    private array $normalizers;

    private Serializer $serializer;

    /**
     * CrudTrickController constructor.
     *
     * @param \App\Repository\TricksRepository          $tricksRepository
     * @param \App\Repository\MediaRepository           $mediaRepository
     * @param \App\Repository\UsersRepository           $usersRepository
     * @param \Twig\Environment                         $twig
     * @param \Doctrine\ORM\EntityManagerInterface      $entityManager
     * @param \Symfony\Component\Security\Core\Security $security
     * @param \App\Repository\CommentsRepository        $commentsRepository
     */
    public function __construct(
        TricksRepository $tricksRepository,
        MediaRepository $mediaRepository,
        UsersRepository $usersRepository,
        Environment $twig,
        EntityManagerInterface $entityManager,
        CommentsRepository $commentsRepository,
        Security $security
    ) {
        $this->tricksRepository = $tricksRepository;
        $this->mediaRepository = $mediaRepository;
        $this->usersRepository = $usersRepository;
        $this->twig = $twig;
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->commentsRepository = $commentsRepository;
        $this->encoders = [new JsonEncoder()];
        $this->normalizers = [new JsonSerializableNormalizer()];
        $this->serializer = new Serializer($this->normalizers, $this->encoders);
    }

    /**
     * @Route("/new", name="trick_new", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $photoDir
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request, string $photoDir): Response
    {
        $trick = new Trick();
        $slugger = new AsciiSlugger();
        $form = $this->createForm(TrickFormType::class, $trick);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $trick->setUser($this->getUser());
                $trick->setSlug($slugger->slug($form->get('name')->getData()));
                $trick->setCreatedAt();
                $trick->setUpdatedAt();
                if ($form->get('img')->getData() != "") {
                    $this->setImage($form->get('img')->getData(), $photoDir, $trick);
                }
                if ($form->get('video')->getData() != "") {
                    $this->setVideo($form->get('video')->getData(), $trick);
                }
                $this->addFlash('success', 'Le trick a bien été enregistré');
                $this->entityManager->persist($trick);
                $this->entityManager->flush();
                return $this->redirectToRoute('accueil');
            } catch (UniqueConstraintViolationException $exception) {
                $this->addFlash('error', 'Un trick portant ce nom existe déjà');
            }
        }
        return $this->render('trick/new.html.twig', ['trickForm' => $form->createView(),]);
    }


    /**
    * @Route("/{slug}/show", name="trick_show")
    * @param string                                    $slug
    * @param \Symfony\Component\HttpFoundation\Request $request
    *
    * @return \Symfony\Component\HttpFoundation\Response
    */
    public function show(string $slug, Request $request): Response
    {
        $trick = $this->tricksRepository->findOneBy(['slug' => $slug]);

        if (!$trick) {
            throw $this->createAccessDeniedException('Ce trick n\'existe pas');
        }

        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUser($this->getUser());
            $comment->setCreatedAt();
            $comment->setUpdatedAt();
            $comment->setTrick($trick);
            if ($comment->getParentId()) {
                $parentComment = $this->commentsRepository->find($comment->getParentId());
                $parentComment->setUpdatedAt();
                $this->entityManager->persist($parentComment);
                $this->entityManager->flush();
            }
            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            return $this->redirectToRoute(
                'trick_show',
                ['slug' => $trick->getSlug(), 'featuredImg' => $trick->getFeaturedImg()]
            );
        }


        return $this->render(
            'trick/index.html.twig',
            [
            'trick' => $trick,
            'offset' => $this->offset,
            'comment_form' => $form->createView(),
            'featuredImg' => $trick->getFeaturedImg()
            ]
        );
    }

    /**
     * @Route("/edit/{slug}", name="trick_edit", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $slug
     * @param string                                    $photoDir
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, string $slug, string $photoDir): Response
    {
        $trick = $this->tricksRepository->findOneBy(['slug' => $slug]);
        $form = $this->createForm(TrickFormType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('img')->getData() != "") {
                $this->setImage($form->get('img')->getData(), $photoDir, $trick, $trick->hasFeaturedImg());
            }
            if ($form->get('video')->getData() != "") {
                $this->setVideo($form->get('video')->getData(), $trick);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('trick_show', ['slug' => $slug]);
        }

        return $this->render('trick/edit.html.twig', [
            'trick' => $trick,
            'trickForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/delete", name="trick_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Trick $trick): Response
    {
        if ($this->isCsrfTokenValid('delete' . $trick->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($trick);
            $entityManager->flush();
        }

        return $this->redirectToRoute('accueil');
    }
}
