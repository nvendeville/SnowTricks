<?php

namespace App\Controller;

use App\Entity\Tricks;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TricksController extends AbstractController
{
    /**
     * @Route("/{slug}", name="tricks")
     * @param $slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index($slug): Response
    {
        $trick = $this->getDoctrine()->getRepository(Tricks::class)->findOneBy(['slug' => $slug]);

        if (!$trick) {
            throw $this->createAccessDeniedException('Ce trick n\'existe pas');
        }
        return $this->render('tricks/index.html.twig', [
            'trick' => $trick,
        ]);
    }
}
