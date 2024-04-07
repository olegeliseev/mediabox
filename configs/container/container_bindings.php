<?php

declare(strict_types=1);

use Twig\Environment;
use Twig\Extension\DebugExtension;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Slim\App;
use App\Config;

use function DI\create;

return [
    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);

        $addMiddlewares = require CONFIG_PATH . '/middleware.php';
        $router         = require CONFIG_PATH . '/routes/web.php';

        $app = AppFactory::create();
        $router($app);
        $addMiddlewares($app);

        return $app;
    },
    Config::class => create(Config::class)->constructor(
        require CONFIG_PATH . '/app.php'
    ),
    Environment::class => function () {
        $loader = new \Twig\Loader\FilesystemLoader(VIEWS_PATH);
        $twig = new \Twig\Environment($loader, [
            'debug' => true
        ]);
        $twig->addExtension(new DebugExtension());
        return $twig;
    },
    Config::class => create(Config::class)->constructor(
        require CONFIG_PATH . '/app.php'
    ),
    EntityManagerInterface::class => function(Config $config) {
        $ormConfig = ORMSetup::createAttributeMetadataConfiguration(
            $config->get('doctrine.entity_dir'),
            $config->get('doctrine.dev_mode')
        );

        return new EntityManager(
            DriverManager::getConnection($config->get('doctrine.connection'), $ormConfig),
            $ormConfig
        );
    },
    ResponseFactoryInterface::class => fn(App $app) => $app->getResponseFactory()
];
