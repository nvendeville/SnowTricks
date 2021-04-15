<?php

namespace App\Service;

use App\Entity\Media;
use App\Entity\Trick;

trait SetVideo
{

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
}
