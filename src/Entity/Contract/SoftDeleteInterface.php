<?php

namespace App\Entity\Contract;

interface SoftDeleteInterface
{
    public function setDeletedAt(\DateTimeImmutable $deletedAt);

    public function getDeletedAt(): ?\DateTimeInterface;
}