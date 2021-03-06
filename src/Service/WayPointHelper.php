<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class WayPointHelper
{
    private string $rootDir;
    private string $intelUrl;
    private UploaderHelper $uploaderHelper;

    public function __construct(
        UploaderHelper $uploaderHelper,
        string $rootDir,
        string $intelUrl
    ) {
        $this->rootDir = $rootDir.'/public/wp_images';
        $this->intelUrl = $intelUrl;
        $this->uploaderHelper = $uploaderHelper;
    }

    public function getImagePath(string $wpId): string
    {
        return $this->rootDir.'/'.$wpId.'.jpg';
    }

    public function findImage(?string $wpId): bool
    {
        if (!$wpId) {
            return false;
        }

        $fileSystem = new Filesystem();

        if (false === $fileSystem->exists($this->rootDir)) {
            $fileSystem->mkdir($this->rootDir);
        }

        $imagePath = $this->getImagePath($wpId);

        if ($fileSystem->exists($imagePath)) {
            return $imagePath;
        }

        return false;

        return $fileSystem->exists($imagePath) ? $imagePath : false;
    }

    private function downloadImage(
        string $wpId,
        string $imageUrl,
        bool $forceUpdate = false
    ): File {
        // $imagePath = $this->findImage($wpId);

        if (false === $forceUpdate && $this->findImage($wpId)) {
            return new File($this->getImagePath($wpId));
        }

        $imagePath = $this->getImagePath($wpId);

        $ch = curl_init($imageUrl);
        $fp = fopen($imagePath, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return new File($imagePath);
    }

    public function processImage(string $wpId, string $imageUrl): string
    {
        $fileName = $wpId.'.jpg';
        // Check if image exists

        // Download image to temp
        $file = $this->downloadImage($wpId, $imageUrl);

        // Upload image
        $this->uploaderHelper->uploadImage($file, null);

        $this->removeImage($file);

        return $file->getFilename();
    }

    private function checkImageExists()
    {
    }

    private function removeImage(File $file): void
    {
        $fileSystem = new Filesystem();

        $fileSystem->remove($file->getFilename());
    }

    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    public function getIntelUrl(): string
    {
        return $this->intelUrl;
    }

    public function cleanName(string $name): string
    {
        $replacements = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'Ó' => 'O',
            'ú' => 'u',
            'Ú' => 'U',
            'ñ' => 'ni',
            'ü' => 'ue',
        ];

        $name = trim($name);
        $name = str_replace(['.', ',', ';', ':', '"', '\'', '\\'], '', $name);

        $name = str_replace(array_keys($replacements), $replacements, $name);

        return $name;
    }
}
