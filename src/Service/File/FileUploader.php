<?php

namespace App\Service\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    public function __construct(
        private readonly string $targetDirectory,
        private readonly FileNameStrategy $fileName,
    ) {}

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileName = $this->fileName->getFileName($originalFilename, $file->guessExtension());

        $file->move($this->getTargetDirectory(), $fileName);

        return $fileName;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}