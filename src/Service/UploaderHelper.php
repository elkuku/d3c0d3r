<?php

namespace App\Service;

use League\Flysystem\AdapterInterface;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploaderHelper
{
    public const WAYPOINT_IMAGE = 'wp_images';
    public const WAYPOINT_REFERENCE = 'wp_reference';

    private RequestStackContext $requestStackContext;
    private FilesystemInterface $filesystem;
    private LoggerInterface $logger;

    private string $publicAssetBaseUrl;

    public function __construct(
        FilesystemInterface $uploadsFilesystem,
        RequestStackContext $requestStackContext,
        LoggerInterface $logger,
        string $uploadedAssetsBaseUrl
    ) {
        $this->requestStackContext = $requestStackContext;
        $this->filesystem = $uploadsFilesystem;
        $this->logger = $logger;
        $this->publicAssetBaseUrl = $uploadedAssetsBaseUrl;
    }

    public function uploadImage(
        File $file,
        ?string $existingFilename
    ): string {
        $newFilename = $this->uploadFile($file, self::WAYPOINT_IMAGE, true);

        if ($existingFilename) {
            try {
                $result = $this->filesystem->delete(
                    self::WAYPOINT_IMAGE.'/'.$existingFilename
                );

                if ($result === false) {
                    throw new \Exception(
                        sprintf(
                            'Could not delete old uploaded file "%s"',
                            $existingFilename
                        )
                    );
                }
            } catch (FileNotFoundException $e) {
                $this->logger->alert(
                    sprintf(
                        'Old uploaded file "%s" was missing when trying to delete',
                        $existingFilename
                    )
                );
            }
        }

        return $newFilename;
    }

    public function uploadArticleReference(File $file): string
    {
        return $this->uploadFile($file, self::WAYPOINT_REFERENCE, false);
    }

    public function getPublicPath(string $path): string
    {
        $add = $path ? '/'.$path : '';

        $fullPath = $this->publicAssetBaseUrl.$add;

        // if it's already absolute, just return
        if (strpos($fullPath, '://') !== false) {
            return $fullPath;
        }

        // needed if you deploy under a subdirectory
        return $this->requestStackContext
                ->getBasePath().$this->publicAssetBaseUrl.$add;
    }

    /**
     * @return resource
     * @throws FileNotFoundException
     */
    public function readStream(string $path)
    {
        $resource = $this->filesystem->readStream($path);

        if ($resource === false) {
            throw new \Exception(
                sprintf('Error opening stream for "%s"', $path)
            );
        }

        return $resource;
    }

    public function deleteFile(string $path): void
    {
        $result = $this->filesystem->delete($path);

        if ($result === false) {
            throw new \Exception(sprintf('Error deleting "%s"', $path));
        }
    }

    private function uploadFile(
        File $file,
        string $directory,
        bool $isPublic
    ): string {
        if ($file instanceof UploadedFile) {
            $originalFilename = $file->getClientOriginalName();
        } else {
            $originalFilename = $file->getFilename();
        }

        // $newFilename = pathinfo($originalFilename, PATHINFO_FILENAME).'-'
        //     .uniqid().'.'.$file->guessExtension();

        $newFilename = $originalFilename;

        if ($this->filesystem->has($directory.'/'.$newFilename)) {
            return $newFilename;
        }

        $stream = fopen($file->getPathname(), 'r');

        // $filesystem = $isPublic ? $this->filesystem : $this->privateFilesystem;

        $result = $this->filesystem->writeStream(
            $directory.'/'.$newFilename,
            $stream,
            [
                'visibility' => $isPublic ? AdapterInterface::VISIBILITY_PUBLIC
                    : AdapterInterface::VISIBILITY_PRIVATE,
            ]
        );

        if ($result === false) {
            throw new \Exception(
                sprintf('Could not write uploaded file "%s"', $newFilename)
            );
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $newFilename;
    }
}
