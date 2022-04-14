<?php

// declare(strict_types=1);

// use Exception;
use Phalcon\Cli\Console;
use Phalcon\Cli\Dispatcher;
use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Exception as PhalconException;
use Phalcon\Loader;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream as logStream;
// use Phalcon\Di\FactoryDefault;

// use Throwable;

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('URL_PATH', "http://localhost:8080");
define('APP_PATH', BASE_PATH . '/app');

require_once(BASE_PATH."/vendor/autoload.php");


$loader = new Loader();
$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        APP_PATH . "/models/",
    ]
);
$loader->registerNamespaces(
    [
        "helper" => APP_PATH . "/helper",
        "models" => APP_PATH . "/models",
        "Console" => APP_PATH . "/console"
    ]
);

$loader->register();

$container  = new CliDI();
$dispatcher = new Dispatcher();

$container->set(
    'objects',
    function () {
        $detail = array(
            'listener' => new \helper\listener()
        );
        return (object)$detail;
    }
);

$container->set(
    'db',
    function () {
        return new Mysql(
            [
                'host'     => 'mysql-server',
                'username' => 'root',
                'password' => 'secret',
                'dbname'   => 'storePhalcon',
            ]
        );
    }
);

// $container->setShared('config', function () {
//     return include APP_PATH.'/storage/config.php';
// });

$container->set(
    'logs',
    function () {
        $logger  = new Logger(
            'messages',
            [
                "main" => new logStream(APP_PATH . "/logs/main.log"),
                "admin" => new logStream(APP_PATH . "/logs/admin.log"),
            ]
        );
        return $logger;
    }
);

// $container->set(
//     'db',
//     function () {
//         $config = $this->get('config');
//         return new Mysql(
//             [
//                 'host'     => $config['db']['host'],
//                 'username' => $config['db']['username'],
//                 'password' => $config['db']['password'],
//                 'dbname'   => $config['db']['dbname'],
//             ]
//         );
//     }
// );

$dispatcher->setDefaultNamespace('Console');
$container->setShared('dispatcher', $dispatcher);



$console = new Console($container);

$arguments = [];
foreach ($argv as $k => $arg) {
    if ($k === 1) {
        $arguments['task'] = $arg;
    } elseif ($k === 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        $arguments['params'][] = $arg;
    }
}

try {
    $console->handle($arguments);
} catch (PhalconException $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
} catch (Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
} catch (Exception $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}