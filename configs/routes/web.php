<?php

declare(strict_types=1);

use App\Controllers\FilesController;
use App\Controllers\HomeController;
use App\Middleware\FileValidationMiddleware;
use App\Middleware\FileExistsMiddleware;
use Slim\App;

return function (App $app) {
    $app->get("/", [HomeController::class, 'index']);
    $app->post("/", [FilesController::class, 'load'])->add(FileValidationMiddleware::class);
    $app->get("/{hash}", [FilesController::class, 'showDownloadPage'])->add(FileExistsMiddleware::class);
    $app->get("/download/{hash}", [FilesController::class, 'startDownload'])->add(FileExistsMiddleware::class);
};