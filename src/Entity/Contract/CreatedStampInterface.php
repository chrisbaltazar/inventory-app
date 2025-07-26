<?php

namespace App\Entity\Contract;

interface CreatedStampInterface
{
    public function setCreatedAt(\DateTimeImmutable $updatedAt);

    public function getCreatedAt(): ?\DateTimeImmutable;
}