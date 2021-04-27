<?php

namespace App\Tests\Entity;

use App\Entity\Media;
use App\Entity\Trick;
use PHPUnit\Framework\TestCase;

class TrickTest extends TestCase
{
    private Trick $trick;

    private function mediaTrue(): Media
    {
        $media = new Media();
        $media->setFeaturedImg(true);
        $media->setLink('monImage.jpg');

        return $media;
    }

    private function mediaFalse(): Media
    {
        $media = new Media();
        $media->setFeaturedImg(false);
        $media->setLink('monImage2.jpg');

        return $media;
    }

    /**
     * @before
     */
    public function setupTrick()
    {
        $this->trick = new Trick();
    }

    public function testDefaultImgWhenNoMediaInTrick()
    {
        self::assertEquals('default_featured_img.jpg', $this->trick->getFeaturedImg());
    }

    public function testGetLinkWhenOneFeaturedImgInMedia()
    {
        $media = $this->mediaTrue();
        $this->trick->addMedium($media);
        self::assertEquals($media->getLink(), $this->trick->getFeaturedImg());
    }

    public function testDefaultImgWhenNoFeaturedImgInMedia()
    {
        $media = $this->mediaFalse();
        $this->trick->addMedium($media);
        self::assertEquals('default_featured_img.jpg', $this->trick->getFeaturedImg());
    }

    public function testResetFeaturedImgWhenOneFeaturedImgInMedia()
    {
        $featuredMedia = $this->mediaTrue();
        $otherMedia = $this->mediaFalse();
        $this->trick->addMedium($featuredMedia);
        $this->trick->addMedium($otherMedia);
        $this->trick->resetFeaturedImg();
        self::assertFalse($featuredMedia->getFeaturedImg());
    }

    public function testSetFeaturedImgWhenNoFeaturedImgInMedia()
    {
        $media = $this->mediaFalse();
        $this->trick->addMedium($media);
        self::assertEquals('default_featured_img.jpg', $this->trick->getFeaturedImg());
        $media->setFeaturedImg(true);
        self::assertEquals($media->getLink(), $this->trick->getFeaturedImg());
    }
}
