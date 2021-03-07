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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Twig\Environment;

/**
 * @Route("/trick")
 */
class CrudTrickController extends AbstractController
{

    private TricksRepository $tricksRepository;

    private MediaRepository $mediaRepository;

    private CommentsRepository $commentsRepository;

    private EntityManagerInterface $entityManager;

    private Environment $twig;

    private UsersRepository $usersRepository;

    private Security $security;

    private int $offset = 0;

    private int $limit = 4;

    private \Symfony\Component\Serializer\Serializer $serializer;

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
            $trick->setUser($this->getUser());
            $trick->setSlug($trick->getId() . "_" . $slugger->slug($form->get('name')->getData()));
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
                $mediaImg->setFeaturedImg(false);
                if ($image == $images[0]) {
                    $mediaImg->setFeaturedImg(true);
                }
                $trick->addMedium($mediaImg);
            }
            /*$videos = $form->get('video')->getData();
            dd($videos);

            foreach ($videos as $video) {
                $mediaVideo = new Media();
                $mediaVideo->setLink($video);

                $mediaVideo->setFeaturedImg(false);

                $trick->addMedium($mediaVideo);
            }*/
            $this->entityManager->persist($trick);
            $this->entityManager->flush();
            return $this->redirectToRoute('accueil');
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

        $date = new \DateTime('Now');
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUser($this->getUser());
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

        return new JsonResponse(['comments' => $this->serializer->serialize($comments, 'json')]);
    }

    /**
     * @Route("/{slug}/edit", name="trick_edit", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $slug
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

            return $this->redirectToRoute('trick_show',['slug'=> $slug]);
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

            $eman = $this->getDoctrine()->getManager();
            $eman->flush();
            $eman->persist($media);

            return new JsonResponse(['success' => 1]);
        }
        return new JsonResponse(['error' => 'Token Invalide'], 400);
    }
}
