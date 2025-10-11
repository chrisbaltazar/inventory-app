<?php

namespace App\Service\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    public function __construct(
        private readonly string $kernelProjectDir,
        private readonly string $targetDirectory,
        private readonly FileNameStrategy $fileName,
    ) {}

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileName = $this->fileName->getFileName($originalFilename, $file->guessExtension());

        $file->move($this->targetDirectory, $fileName);

        return $this->buildFilePath($fileName);
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }

    protected function buildFilePath(string $fileName): string
    {
        $cleanPath = [$this->kernelProjectDir, 'public'];
        $filePath = sprintf('%s/%s', $this->targetDirectory, $fileName);

        foreach ($cleanPath as $path) {
            $filePath = ltrim($filePath, $path);
            $filePath = ltrim($filePath, '/');
        }

        return $filePath;
    }

}