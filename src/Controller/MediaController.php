<?php

namespace App\Controller;

use App\Entity\Media;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MediaController extends AbstractController
{
    /**
     * @Route("/supprime/image/{id}", name="trick_delete_image", methods={"DELETE"})
     * @param \App\Entity\Media                         $media
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $photoDir
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteMedia(Media $media, Request $request, string $photoDir): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($this->isCsrfTokenValid('delete' . $media->getId(), $data['_token'])) {
            $link = $media->getLink();
            if ($media->getType() == "image") {
                unlink($photoDir . '/' . $link);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($media);
            $entityManager->flush();

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

        if ($this->isCsrfTokenValid('feature' . $media->getId(), $data['_token'])) {
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
