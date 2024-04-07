<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\File;
use App\DataObjects\FileData;
use Doctrine\ORM\EntityManagerInterface;
use Slim\Psr7\UploadedFile;
use ZipArchive;

class FileService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function create(FileData $fileData): File
    {
        $file = new File();

        return $this->update($file, $fileData);
    }

    public function update(File $file, FileData $fileData): File
    {
        $file->setHash($fileData->hash);
        $file->setFileName($fileData->fileName);
        $file->setExtension($fileData->extension);
        $file->setFileSize($fileData->fileSize);
        $file->setIsImg($fileData->isImg);
        $file->setCreatedAt($fileData->createdAt);

        return $file;
    }

    public function getByHash(string $hash): ?File
    {
        $file = $this->entityManager->getRepository('App\Entities\File')->findOneBy(array('hash' => $hash));

        return $file;
    }

    public function uploadSingleFile(UploadedFile $uploadedFile): File
    {
        $uploadedFileExtension = pathinfo($uploadedFile->getClientFileName(), PATHINFO_EXTENSION);
        $imgExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($uploadedFileExtension, $imgExtensions)) {
            $isImg = true;
        } else {
            $isImg = false;
        }

        $file = $this->create(
            new FileData(
                bin2hex(random_bytes(8)),
                pathinfo($uploadedFile->getClientFileName(), PATHINFO_BASENAME),
                $uploadedFileExtension,
                $uploadedFile->getSize(),
                $isImg,
                new \DateTime('now')
            )
        );

        $this->entityManager->persist($file);
        $this->entityManager->flush();

        mkdir(STORAGE_PATH . '/app/uploads' . DIRECTORY_SEPARATOR . $file->getHash());
        $uploadedFile->moveTo(STORAGE_PATH . '/app/uploads' . DIRECTORY_SEPARATOR . $file->getHash() . DIRECTORY_SEPARATOR . $file->getFileName());

        return $file;
    }

    public function uploadZipArchive(array $uploadedFiles): File
    {
        $zip = new ZipArchive();
        $zipFileName = bin2hex(random_bytes(8)) . '.zip';
        $zipHex = bin2hex(random_bytes(8));

        mkdir(STORAGE_PATH . '/app/uploads' . DIRECTORY_SEPARATOR . $zipHex);
        $zipStoragePath = STORAGE_PATH . '/app/uploads' . DIRECTORY_SEPARATOR . $zipHex . DIRECTORY_SEPARATOR . $zipFileName;

        $zip->open($zipStoragePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($uploadedFiles as $uploadedFile) {
            $zip->addFile(
                $uploadedFile->getFilePath(),
                $uploadedFile->getClientFilename()
            );
        }
        $zip->close();

        $file = $this->create(
            new FileData(
                $zipHex, //hex
                $zipFileName, //fileName
                'zip',
                filesize($zipStoragePath),
                false, //isImg
                new \DateTime('now')
            )
        );

        $this->entityManager->persist($file);
        $this->entityManager->flush();

        return $file;
    }
}
