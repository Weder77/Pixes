<?php

namespace App\Entity;

use App\Repository\OpinionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OpinionRepository::class)
 */
class Opinion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="integer")
     */
    private $note;

    /**
     * @ORM\Column(type="datetime")
     */
    private $posted_on;

    /**
     * @ORM\ManyToOne(targetEntity=Game::class, inversedBy="opinions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $game;

    /**
     * @ORM\ManyToOne(targetEntity=Profile::class, inversedBy="opinions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getNote(): ?float
    {
        return $this->note;
    }

    public function setNote(float $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getPostedOn(): ?\DateTimeInterface
    {
        return $this->posted_on;
    }

    public function setPostedOn(\DateTimeInterface $posted_on): self
    {
        $this->posted_on = $posted_on;

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

    public function getUser(): ?Profile
    {
        return $this->user;
    }

    public function setUser(?Profile $user): self
    {
        $this->user = $user;

        return $this;
    }
}
