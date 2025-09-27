<?php

namespace App\Service\File;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    public function __construct(
        private readonly string $targetDirectory,
    ) {
    }


    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        return $fileName;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}