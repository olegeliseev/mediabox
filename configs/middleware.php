<?php

declare(strict_types=1);

use Slim\App;
use App\Config;

return function (App $app) {
    $container = $app->getContainer();
    $config    = $container->get(Config::class);
    $app->addRoutingMiddleware(); 
    $app->addErrorMiddleware(
        (bool) $config->get('display_error_details'),
        (bool) $config->get('log_errors'),
        (bool) $config->get('log_error_details')
    );
};