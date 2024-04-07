<?php

declare(strict_types=1);

use Slim\App;

$container = require '../bootstrap.php';
$container->get(App::class)->run();