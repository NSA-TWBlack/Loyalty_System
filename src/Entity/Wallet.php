<?php

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WalletRepository::class)]
#[ORM\Table(name: 'wallets')]
class Wallet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'wallet', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Member $member = null;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, options: ['default' => 0])]
    private ?string $balance = '0';

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'wallet', targetEntity: Point::class)]
    private Collection $points;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
        $this->points = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getMember(): ?Member { return $this->member; }
    public function setMember(Member $member): static
    {
        $this->member = $member;
        return $this;
    }

    public function getBalance(): ?string { return $this->balance; }
    public function setBalance(string $balance): static
    {
        $this->balance = $balance;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getPoints(): Collection { return $this->points; }
}