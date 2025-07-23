<?php

namespace App\Entity;

interface UserAwareInterface
{
    public function setUpdatedBy(User $updatedBy);

    public function getUpdatedBy();
}