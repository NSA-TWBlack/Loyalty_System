<?php

namespace App\Entity;

use App\Repository\GiftRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GiftRepository::class)]
#[ORM\Table(name: 'gifts')]
class Gift
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $giftName = null;

    #[ORM\Column(type: 'integer')]
    private ?int $pointCost = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private ?int $stock = 0;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\OneToMany(mappedBy: 'gift', targetEntity: Redemption::class)]
    private Collection $redemptions;

    public function __construct()
    {
        $this->redemptions = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getGiftName(): ?string { return $this->giftName; }
    public function setGiftName(string $giftName): static
    {
        $this->giftName = $giftName;
        return $this;
    }

    public function getPointCost(): ?int { return $this->pointCost; }
    public function setPointCost(int $pointCost): static
    {
        $this->pointCost = $pointCost;
        return $this;
    }

    public function getStock(): ?int { return $this->stock; }
    public function setStock(int $stock): static
    {
        $this->stock = $stock;
        return $this;
    }

    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getRedemptions(): Collection { return $this->redemptions; }
}