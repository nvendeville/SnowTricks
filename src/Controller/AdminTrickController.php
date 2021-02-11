<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Trick;
use App\Form\TrickFormType;
use App\Repository\CommentsRepository;
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
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Serializer;
use Twig\Environment;
use Symfony\Component\HttpFoundation\File\File;

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
        $date = new \DateTime('Now');
        $form = $this->createForm(TrickFormType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick->setUser($this->getUser());
            $trick->setCreatedAt($date);
            $trick->setUpdatedAt($date);
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
            $this->entityManager->persist($trick);
            $this->entityManager->flush();

            return $this->redirectToRoute('accueil');
        }
        return $this->render('admin_trick/index.html.twig', ['adminTrickForm' => $form->createView(),]);
    }

    public function edit(Request $request, Trick $trick, string $photoDir)
    {
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
