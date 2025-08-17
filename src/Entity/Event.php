<?php

namespace App\Entity;

use App\Entity\Contract\SoftDeleteInterface;
use App\Entity\Contract\UpdatedStampInterface;
use App\Entity\Contract\UserAwareInterface;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Index(name: 'events_return_date_idx', columns: ['return_date'])]
#[ORM\Index(name: 'events_deleted_idx', columns: ['deleted_at'])]
class Event implements UpdatedStampInterface, SoftDeleteInterface, UserAwareInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank()]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deliveryDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $returnDate = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\Column]
    private ?bool $public = null;

    #[ORM\ManyToOne]
    private ?User $updatedBy = null;

    #[ORM\OneToMany(targetEntity: Loan::class, mappedBy: 'Event', orphanRemoval: true)]
    private Collection $loans;

    public function __construct()
    {
        $this->loans = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDeliveryDate(): ?\DateTimeInterface
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(?\DateTimeInterface $deliveryDate): static
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    public function getReturnDate(): ?\DateTimeInterface
    {
        return $this->returnDate;
    }

    public function setReturnDate(?\DateTimeInterface $returnDate): static
    {
        $this->returnDate = $returnDate;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(\DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): static
    {
        $this->public = $public;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(User $updatedBy): static
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * @return Collection<int, Loan>
     */
    public function getLoans(): Collection
    {
        return $this->loans;
    }

    public function addLoan(Loan $loan): static
    {
        if (!$this->loans->contains($loan)) {
            $this->loans->add($loan);
            $loan->setEvent($this);
        }

        return $this;
    }

    public function removeLoan(Loan $loan): static
    {
        if ($this->loans->removeElement($loan)) {
            // set the owning side to null (unless already changed)
            if ($loan->getEvent() === $this) {
                $loan->setEvent(null);
            }
        }

        return $this;
    }
}
