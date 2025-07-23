<?php

namespace App\Entity;

interface CreatedAwareInterface
{
    public function setCreatedAt(\DateTimeImmutable $updatedAt);

    public function getCreatedAt(): ?\DateTimeImmutable;
}