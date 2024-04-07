<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment as Twig;

class HomeController
{
    public function __construct(
        private readonly Twig $twig,
        )
    {
    }

    public function index(Request $request, Response $response): Response
    {
        $templateStr = $this->twig->render('index.twig');
        $response->getBody()->write($templateStr);
        return $response;
    }
}
