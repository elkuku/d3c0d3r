<?php

namespace App\Service;

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
    /**
     * @var RequestStackContext
     */
    private RequestStackContext $requestStackContext;
    /**
     * @var FilesystemInterface
     */
    private FilesystemInterface $filesystem;
    private FilesystemInterface $privateFilesystem;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    private string $publicAssetBaseUrl;

    public function __construct(
        FilesystemInterface $publicUploadsFilesystem,
        FilesystemInterface $privateUploadsFilesystem,
        RequestStackContext $requestStackContext,
        LoggerInterface $logger,
        string $uploadedAssetsBaseUrl
    ) {
        $this->requestStackContext = $requestStackContext;
        $this->filesystem = $publicUploadsFilesystem;
        $this->privateFilesystem = $privateUploadsFilesystem;
        $this->logger = $logger;
        $this->publicAssetBaseUrl = $uploadedAssetsBaseUrl;
    }

    public function uploadArticleImage(
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

    private function uploadFile(File $file, string $directory, bool $isPublic)
    {
        if ($file instanceof UploadedFile) {
            $originalFilename = $file->getClientOriginalName();
        } else {
            $originalFilename = $file->getFilename();
        }
        $newFilename = pathinfo($originalFilename, PATHINFO_FILENAME).'-'
            .uniqid().'.'.$file->guessExtension();
        $stream = fopen($file->getPathname(), 'r');
        $filesystem = $isPublic ? $this->filesystem : $this->privateFilesystem;
        $result = $filesystem->writeStream(
            $directory.'/'.$newFilename,
            $stream
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

    public function getPublicPath(string $path): string
    {
        return $this->requestStackContext
                ->getBasePath().$this->publicAssetBaseUrl.'/'.$path;
    }

    /**
     * @return resource
     */
    public function readStream(string $path, bool $isPublic)
    {
        $filesystem = $isPublic ? $this->filesystem : $this->privateFilesystem;

        $resource = $filesystem->readStream($path);

        if ($resource === false) {
            throw new \Exception(sprintf('Error opening stream for "%s"', $path));
        }
        return $resource;
    }

    public function deleteFile(string $path, bool $isPublic)
    {
        $filesystem = $isPublic ? $this->filesystem : $this->privateFilesystem;

        $result = $filesystem->delete($path);

        if ($result === false) {
            throw new \Exception(sprintf('Error deleting "%s"', $path));
        }
    }
}
