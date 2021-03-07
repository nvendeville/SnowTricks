<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Trick;
use App\Form\TrickFormType;
use App\Repository\MediaRepository;
use App\Repository\TricksRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Twig\Environment;

/**
 * Class AdminTrickController
 * @package App\Controller
 */
class AdminTrickController extends AbstractController
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
     * @var \Doctrine\ORM\EntityManagerInterface
     */
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
     * @var \Symfony\Component\Security\Core\Security
     */
    private $security;

    /**
     * AdminTrickController constructor.
     *
     * @param \App\Repository\TricksRepository          $tricksRepository
     * @param \App\Repository\MediaRepository           $mediaRepository
     * @param \App\Repository\UsersRepository           $usersRepository
     * @param \Twig\Environment                         $twig
     * @param \Doctrine\ORM\EntityManagerInterface      $entityManager
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(TricksRepository $tricksRepository, MediaRepository $mediaRepository,
        UsersRepository $usersRepository, Environment $twig, EntityManagerInterface $entityManager, Security $security)
    {
        $this->tricksRepository = $tricksRepository;
        $this->mediaRepository = $mediaRepository;
        $this->usersRepository = $usersRepository;
        $this->twig = $twig;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * @Route("/admin/trick", name="admin_trick")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $photoDir
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function index(Request $request, string $photoDir): Response
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
        return $this->render('admin_trick/index.html.twig', ['adminTrickForm' => $form->createView(),]);
    }

    /**
     * @Route("/admin/trick/{slug}/edit", name="admin_trick_edit")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $photoDir
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function edit(Request $request, Trick $trick, string $photoDir, string $slug)
    {
        $trick = new Trick();

        $form = $this->createForm(TrickFormType::class, $trick);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->isSubmitted() && $form->isValid()) {
                $images = $form->get('media')->getData();
                foreach ($images as $image) {
                    $filename = md5(uniqid()) . '.' . $image->guessExtension();
                    try {
                        $image->move($photoDir, $filename);
                    } catch (FileException $e) {
                        // unable to upload the photo, give up
                    }
                    $media = new Media();
                    $media->setLink($filename);
                    $media->setFeaturedImg(false);
                    $trick->addMedium($media);
                }
                $this->getDoctrine()->getManager()->flush();
                return $this->redirectToRoute('accueil');
            }
        }
        return $this->render('admin_trick/edit.html.twig', ['trick' => $trick, 'form' => $form->createView(),]);
    }
}
