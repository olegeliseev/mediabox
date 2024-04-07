<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment as Twig;
use App\Services\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Slim\Psr7\Factory\StreamFactory;
use Doctrine\Common\Collections\ArrayCollection;
use ZipArchive;
use App\Services\RequestService;

class FilesController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly FileService $fileService,
        private readonly EntityManagerInterface $entityManager,
        private readonly StreamFactory $streamFactory,
        private readonly ArrayCollection $filesCollection,
        private readonly ZipArchive $zipArchive,
        private readonly RequestService $requestService
    ) {
    }

    public function load(Request $request, Response $response): Response
    {
        if ($this->requestService->isXhr($request)) {
            $templateStr = $this->twig->render('index.twig');
            $response->getBody()->write($templateStr);
            return $response;
        }

        $uploadedFiles = ($request->getUploadedFiles())['files'];
        $imgOutput = '';

        if (count($uploadedFiles) > 1) {

            $file = $this->fileService->uploadZipArchive($uploadedFiles);
        } else {

            $file = $this->fileService->uploadSingleFile($uploadedFiles[0]);

            if ($file->getIsImg()) {
                $fileStoragePath = STORAGE_PATH . '/app/uploads' . DIRECTORY_SEPARATOR . $file->getHash() . DIRECTORY_SEPARATOR . $file->getFileName();
                $b64image = base64_encode(file_get_contents($fileStoragePath));
                $imgExtension = $file->getExtension();
                $imgOutput = "<img class='imgOutput' src='data:image/$imgExtension;base64,$b64image'>";
            }
        }

        $templateStr = $this->twig->render('loadedFiles.twig', [
            'file' => $file,
            'imgOutput' => $imgOutput
        ]);

        $response->getBody()->write($templateStr);

        return $response;
    }

    public function showDownloadPage(Request $request, Response $response, array $args): Response
    {

        $file = $this->fileService->getByHash($args['hash']);
        $zippedFiles = [];
        $fileStoragePath = STORAGE_PATH . '/app/uploads' . DIRECTORY_SEPARATOR . $file->getHash() . DIRECTORY_SEPARATOR . $file->getFileName();
        
        if($file->getExtension() === 'zip') {
            $this->zipArchive->open($fileStoragePath);
            for ($i = 0; $i < $this->zipArchive->numFiles; $i++) {
                $item = $this->zipArchive->statIndex($i);
                $zippedFiles[] = [
                    'name' => $item['name'],
                    'size' => $item['size']
                ];
            }    
            $this->zipArchive->close();
        }
        
        $templateStr = $this->twig->render('downloadFiles.twig', ['file' => $file, 'zippedFiles' => $zippedFiles]);
        $response->getBody()->write($templateStr);
        return $response;
    }

    public function startDownload(Request $request, Response $response, array $args): Response
    {

        $file = $this->fileService->getByHash($args['hash']);
        $fileName = $file->getFileName();
        $hex = $file->getHash();
        $filePath = STORAGE_PATH . "/app/uploads/$hex/$fileName";

        if (!file_exists($filePath)) {
            return $response;
        }

        $stream = $this->streamFactory->createStreamFromFile($filePath);

        return $response->withHeader('Content-Type', 'application/force-download')
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Type', 'application/download')
            ->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->withHeader('Pragma', 'public')
            ->withBody($stream);
    }
}
