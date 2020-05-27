<?php

namespace App\Entity;

use App\Entity\Profile;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\BuyRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=BuyRepository::class)
 */
class Buy
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="datetime")
     */
    private $purchase_date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url_invoice;

    /**
     * @ORM\ManyToOne(targetEntity=Profile::class, inversedBy="buys")
     * @ORM\JoinColumn(nullable=false)
     */
    private $profile;

    /**
     * @ORM\OneToMany(targetEntity=Code::class, mappedBy="buy")
     */
    private $codes;

    public function __construct()
    {
        $this->codes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPurchaseDate(): ?\DateTimeInterface
    {
        return $this->purchase_date;
    }

    public function setPurchaseDate(\DateTimeInterface $purchase_date): self
    {
        $this->purchase_date = $purchase_date;

        return $this;
    }

    public function getUrlInvoice(): ?string
    {
        return $this->url_invoice;
    }

    public function setUrlInvoice(string $url_invoice): self
    {
        $this->url_invoice = $url_invoice;

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return Collection|Code[]
     */
    public function getCodes(): Collection
    {
        return $this->codes;
    }

    public function addCode(Code $code): self
    {
        if (!$this->codes->contains($code)) {
            $this->codes[] = $code;
            $code->setBuy($this);
        }

        return $this;
    }

    public function removeCode(Code $code): self
    {
        if ($this->codes->contains($code)) {
            $this->codes->removeElement($code);
            // set the owning side to null (unless already changed)
            if ($code->getBuy() === $this) {
                $code->setBuy(null);
            }
        }

        return $this;
    }
}
