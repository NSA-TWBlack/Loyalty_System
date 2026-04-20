<?php

namespace App\Entity;

use App\Repository\PointRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PointRepository::class)]
#[ORM\Table(name: 'points')]
class Point
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'points')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Wallet $wallet = null;

    #[ORM\OneToOne(inversedBy: 'point')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Transaction $transaction = null;

    #[ORM\ManyToOne(inversedBy: 'points')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Redemption $redemption = null;

    #[ORM\Column(type: 'integer')]
    private ?int $pointAmount = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getWallet(): ?Wallet { return $this->wallet; }
    public function setWallet(Wallet $wallet): static
    {
        $this->wallet = $wallet;
        return $this;
    }

    public function getTransaction(): ?Transaction { return $this->transaction; }
    public function setTransaction(?Transaction $transaction): static
    {
        $this->transaction = $transaction;
        return $this;
    }

    public function getRedemption(): ?Redemption { return $this->redemption; }
    public function setRedemption(?Redemption $redemption): static
    {
        $this->redemption = $redemption;
        return $this;
    }

    public function getPointAmount(): ?int { return $this->pointAmount; }
    public function setPointAmount(int $pointAmount): static
    {
        $this->pointAmount = $pointAmount;
        return $this;
    }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
}