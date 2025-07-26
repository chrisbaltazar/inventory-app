<?php

namespace App\Entity\Contract;

interface UpdatedStampInterface
{
    public function setUpdatedAt(\DateTimeImmutable $updatedAt);

    public function getUpdatedAt(): ?\DateTimeImmutable;
}