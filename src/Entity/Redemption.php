<?php

namespace App\Entity;

use App\Repository\RedemptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RedemptionRepository::class)]
#[ORM\Table(name: 'redemptions')]
class Redemption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'redemptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Member $member = null;

    #[ORM\ManyToOne(inversedBy: 'redemptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Gift $gift = null;

    #[ORM\Column(type: 'integer')]
    private ?int $pointsUsed = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'redemption', targetEntity: Point::class)]
    private Collection $points;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->points = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getMember(): ?Member { return $this->member; }
    public function setMember(Member $member): static
    {
        $this->member = $member;
        return $this;
    }

    public function getGift(): ?Gift { return $this->gift; }
    public function setGift(Gift $gift): static
    {
        $this->gift = $gift;
        return $this;
    }

    public function getPointsUsed(): ?int { return $this->pointsUsed; }
    public function setPointsUsed(int $pointsUsed): static
    {
        $this->pointsUsed = $pointsUsed;
        return $this;
    }

    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getPoints(): Collection { return $this->points; }
}