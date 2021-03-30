<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Media;
use App\Entity\Trick;
use App\Form\CommentFormType;
use App\Form\TrickFormType;
use App\Repository\CommentsRepository;
use App\Repository\MediaRepository;
use App\Repository\TricksRepository;
use App\Repository\UsersRepository;
use App\Service\HasMore;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    private TricksRepository $tricksRepository;

    private MediaRepository $mediaRepository;

    private CommentsRepository $commentsRepository;

    private EntityManagerInterface $entityManager;

    private Environment $twig;

    private UsersRepository $usersRepository;

    private Security $security;

    private int $offset = 0;

    private int $limit = 4;
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
     * AdminTrickController constructor.
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
                $images = $form->get('img')->getData();
                foreach ($images as $image) {
                    $mediaImg = new Media();
                    $filenameImg = md5(uniqid()) . '.' . $image->guessExtension();
                    try {
                        $image->move($photoDir, $filenameImg);
                    } catch (FileException $e) {
                        // unable to upload the photo, give up
                    }
                    $mediaImg->setLink($filenameImg);
                    $mediaImg->setType('image');
                    $mediaImg->setFeaturedImg(false);
                    if ($image == $images[0]) {
                        $mediaImg->setFeaturedImg(true);
                    }
                    $trick->addMedium($mediaImg);
                }
                if ($form->get('video')->getData() != "") {
                    $this->setVideo($form->get('video')->getData(), $trick);
                }
                $this->addFlash('success', 'Le trick a bien été enregistré');
                $this->entityManager->persist($trick);
                $this->entityManager->flush();
                return $this->redirectToRoute('accueil');
            } catch (UniqueConstraintViolationException $e) {
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
            $comment->setTrick($trick);

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            return $this->redirectToRoute('trick_show', ['slug' => $trick->getSlug(), 'featuredImg' => $trick->getFeaturedImg()]);
        }

        return $this->render('trick/index.html.twig', [
            'trick' => $trick, 'offset' => $this->offset, 'comment_form' => $form->createView(), 'featuredImg' => $trick->getFeaturedImg()]);
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

        $this->offset = $request->query->getInt('offset');

        $comments = $this->commentsRepository->findBy(
            ['trick' => $this->tricksRepository->findOneBy(['slug' => $slug])],
            ['created_at' => 'desc'],
            $this->limit,
            $this->offset
        );

        $hasMore = $this->hasMore($this->commentsRepository, count($comments), $this->limit, $this->offset + $this->limit);

        return new JsonResponse([
            'comments' => $this->serializer->serialize($comments, 'json'),
            'hasMore' => $hasMore
        ]);
    }

    public function setVideo(string $formVideos, Trick $trick)
    {
        $videos = explode("\n", str_replace("\r", "", $formVideos));
        foreach ($videos as $video) {
            $mediaVideo = new Media();
            $explode = explode("?", $video);
            $type = explode(".", $explode[0]);
            $mediaVideo->setType($type[1]);
            $link = explode("=", $explode[1]);
            $link1 = explode("&", $link[1]);
            $mediaVideo->setLink($link1[0]);
            $mediaVideo->setFeaturedImg(false);
            $trick->addMedium($mediaVideo);
            $trick->setUpdatedAt();
        }
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
            $images = $form->get('img')->getData();
            $videos = $form->get('video')->getData();
            if ($videos != "") {
                $this->setVideo($videos, $trick);
            }
            foreach ($images as $image) {
                $mediaImg = new Media();
                $filenameImg = md5(uniqid()) . '.' . $image->guessExtension();
                try {
                    $image->move($photoDir, $filenameImg);
                } catch (FileException $e) {
                    // unable to upload the photo, give up
                }
                $mediaImg->setLink($filenameImg);
                $mediaImg->setFeaturedImg(false);
                if ($image == $images[0]) {
                    $mediaImg->setFeaturedImg(true);
                }
                $mediaImg->setType('image');
                $trick->addMedium($mediaImg);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('trick_show', ['slug'=> $slug]);
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
        if ($this->isCsrfTokenValid('delete'.$trick->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($trick);
            $entityManager->flush();
        }

        return $this->redirectToRoute('accueil');
    }

    /**
     * @Route("/supprime/image/{id}", name="trick_delete_image", methods={"DELETE"})
     * @param \App\Entity\Media                         $media
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $photoDir
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteImage(Media $media, Request $request, string $photoDir): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($this->isCsrfTokenValid('delete'.$media->getId(), $data['_token'])) {
            $link = $media->getLink();
            unlink($photoDir.'/'.$link);

            $em = $this->getDoctrine()->getManager();
            $em->remove($media);
            $em->flush();

            return new JsonResponse(['success' => 1]);
        }
            return new JsonResponse(['error' => 'Token Invalide'], 400);
    }

    /**
     * @Route("/feature/image/{id}", name="trick_feature_image", methods={"PATCH"})
     * @param \App\Entity\Media                         $media
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function featureImage(Media $media, Request $request): JsonResponse
    {
        $trick = $media->getTrick();
        $trick->resetFeaturedImg();

        $data = json_decode($request->getContent(), true);

        if ($this->isCsrfTokenValid('feature'.$media->getId(), $data['_token'])) {
            $media->setFeaturedImg(true);
            $trick->setUpdatedAt();

            $eman = $this->getDoctrine()->getManager();
            $eman->flush();
            $eman->persist($media);

            return new JsonResponse(['success' => 1]);
        }
        return new JsonResponse(['error' => 'Token Invalide'], 400);
    }
}
