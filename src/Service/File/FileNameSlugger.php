<?php

namespace App\Service\File;

use Symfony\Component\String\Slugger\SluggerInterface;

class FileNameSlugger implements FileNameStrategy
{

    public function __construct(
        private readonly SluggerInterface $slugger,
    ) {}

    public function getFileName(string $originalName, string $extension): string
    {
        return sprintf('%s.%s', $this->slugger->slug($originalName), $extension);
    }
}