<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MediaRepository::class)
 */
class Media
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $featuredImg;

    /**
     * @ORM\Column(type="text", length=65535)
     */
    private ?string $link;

    /**
     * @ORM\ManyToOne(targetEntity=Trick::class, inversedBy="media")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Trick $trick;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $type;

    /**
     * Constructor
     *
     * @param \App\Entity\Trick|null $trick
     */
    public function __construct(Trick $trick = null)
    {
        $this->trick = $trick;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFeaturedImg(): ?bool
    {
        return $this->featuredImg;
    }

    public function setFeaturedImg(bool $featuredImg): self
    {
        $this->featuredImg = $featuredImg;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
