<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FileValidationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory
    ) {   
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if(!$request->getUploadedFiles()['files'][0]->getSize()) {
            $response = $this->responseFactory->createResponse();
            return $response
                ->withHeader('Location', '/')
                ->withStatus(302); 
        }

        return $handler->handle($request);
    }
}