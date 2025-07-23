<?php

namespace App\Entity;

interface UpdatedAwareInterface
{
    public function setUpdatedAt(\DateTimeImmutable $updatedAt);

    public function getUpdatedAt(): ?\DateTimeImmutable;
}