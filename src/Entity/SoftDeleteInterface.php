<?php

namespace App\Entity;

interface SoftDeleteInterface
{
    public function setDeletedAt(?\DateTimeImmutable $deletedAt): void;

    public function getDeletedAt(): ?\DateTimeImmutable;
}