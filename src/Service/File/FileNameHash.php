<?php

namespace App\Service\File;

class FileNameHash implements FileNameStrategy
{
    public function getFileName(string $originalName, string $extension): string
    {
        return md5($originalName) . '.' . $extension;
    }
}