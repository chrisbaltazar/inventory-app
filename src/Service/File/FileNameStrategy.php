<?php

namespace App\Service\File;

interface FileNameStrategy {
    public function getFileName(string $originalName, string $extension): string;
}