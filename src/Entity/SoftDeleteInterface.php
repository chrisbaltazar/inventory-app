<?php

namespace App\Entity;

interface SoftDeleteInterface
{
    public function setDeletedAt(\DateTimeImmutable $deletedAt);

    public function getDeletedAt(): ?\DateTimeImmutable;
}