<?php

namespace App\Entity;

use App\Entity\Contract\SoftDeleteInterface;
use App\Entity\Contract\UpdatedStampInterface;
use App\Entity\Contract\UserAwareInterface;
use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
#[ORM\Index(name: 'item_region_idx', columns: ['region'])]
#[ORM\Index(name: 'item_deleted_idx', columns: ['deleted_at'])]
class Item implements UpdatedStampInterface, SoftDeleteInterface, UserAwareInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Assert\Length(max: 255)]
    private ?string $region = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\OneToMany(targetEntity: Metadata::class, mappedBy: 'item')]
    private Collection $metadata;

    #[ORM\OneToMany(targetEntity: Inventory::class, mappedBy: 'item', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Assert\Valid]
    private Collection $inventory;

    #[ORM\ManyToOne]
    private ?User $updatedBy = null;

    #[ORM\OneToMany(targetEntity: Loan::class, mappedBy: 'item')]
    private Collection $loans;

    public function __construct()
    {
        $this->metadata = new ArrayCollection();
        $this->inventory = new ArrayCollection();
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

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;

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

    /**
     * @return Collection<int, Metadata>
     */
    public function getMetadata(): Collection
    {
        return $this->metadata;
    }

    public function addMetadata(Metadata $metadata): static
    {
        if (!$this->metadata->contains($metadata)) {
            $this->metadata->add($metadata);
            $metadata->setItem($this);
        }

        return $this;
    }

    public function removeMetadata(Metadata $metadata): static
    {
        if ($this->metadata->removeElement($metadata)) {
            // set the owning side to null (unless already changed)
            if ($metadata->getItem() === $this) {
                $metadata->setItem(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Inventory>
     */
    public function getInventory(): Collection
    {
        return $this->inventory;
    }

    public function addInventory(Inventory $inventory): static
    {
        if (!$this->inventory->contains($inventory)) {
            $this->inventory->add($inventory);
            $inventory->setItem($this);
        }

        return $this;
    }

    public function removeInventory(Inventory $inventory): static
    {
        if ($this->inventory->removeElement($inventory)) {
            // set the owning side to null (unless already changed)
            if ($inventory->getItem() === $this) {
                $inventory->setItem(null);
            }
        }

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

    public function getTotal(): int
    {
        return array_reduce($this->inventory->toArray(), function (float $carry, Inventory $inventory) {
            return $carry + $inventory->getQuantity();
        }, 0);
    }

    public function getSizes(): array
    {
        return array_unique(array_map(fn($i) => $i->getSize(), $this->inventory->toArray()));
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
            $loan->setItem($this);
        }

        return $this;
    }

    public function removeLoan(Loan $loan): static
    {
        if ($this->loans->removeElement($loan)) {
            // set the owning side to null (unless already changed)
            if ($loan->getItem() === $this) {
                $loan->setItem(null);
            }
        }

        return $this;
    }
}
