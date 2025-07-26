<?php

namespace App\Entity\Contract;

use App\Entity\User;

interface UserAwareInterface
{
    public function setUpdatedBy(User $updatedBy);

    public function getUpdatedBy();
}