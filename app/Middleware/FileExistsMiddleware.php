<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;   
use App\Services\FileService;

class FileExistsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly FileService $fileService
    ) {   
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeContext = RouteContext::fromRequest($request);                                                                                                         
        $route = $routeContext->getRoute();                                                                                                                                
        $hashValue = $route->getArgument('hash');

        if(!$this->fileService->getByHash($hashValue)) {
            throw new HttpNotFoundException($request);
        }

        return $handler->handle($request);
    }
}