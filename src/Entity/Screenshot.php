<?php

namespace App\Entity;

use App\Repository\ScreenshotRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ScreenshotRepository::class)
 */
class Screenshot
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url_screenshot;

    /**
     * @ORM\ManyToOne(targetEntity=Game::class, inversedBy="screenshots")
     */
    private $game;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrlScreenshot(): ?string
    {
        return $this->url_screenshot;
    }

    public function setUrlScreenshot(string $url_screenshot): self
    {
        $this->url_screenshot = $url_screenshot;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

        return $this;
    }
}
