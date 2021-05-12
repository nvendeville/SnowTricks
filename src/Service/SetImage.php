<?php

namespace App\Service;

use App\Entity\Media;
use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

trait SetImage
{

    public function setImage(array $formImages, string $photoDir, Trick $trick, bool $hasFeaturedImg = false)
    {
        foreach ($formImages as $image) {
            $mediaImg = new Media();
            $filenameImg = md5(uniqid()) . '.' . $image->guessExtension();
            try {
                $image->move($photoDir, $filenameImg);
            } catch (FileException $exception) {
                // unable to upload the photo, give up
            }
            $mediaImg->setLink($filenameImg);
            $mediaImg->setType('image');
            $mediaImg->setFeaturedImg(false);
            if (!$hasFeaturedImg && $image == $formImages[0]) {
                $mediaImg->setFeaturedImg(true);
            }
            $trick->addMedium($mediaImg);
            $trick->setUpdatedAt();
        }
    }
}
