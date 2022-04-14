<?php
// print_r(apache_get_modules());
// echo "<pre>"; print_r($_SERVER); die;
// $_SERVER["REQUEST_URI"] = str_replace("/phalt/","/",$_SERVER["REQUEST_URI"]);
// $_GET["_url"] = "/";

use helper\listener;
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Config;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream as logStream;
use Phalcon\Events\Manager as EventsManager;

$config = new Config([]);

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('BEARER', 'bearer');

require_once(BASE_PATH."/vendor/autoload.php");


/*************************************Loader Start********************************** */
// Register an autoloader
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
        // "models" => APP_PATH . "/models"
    ]
);

$loader->register();
/*************************************Loader End********************************** */


/******************************Events Start******************************** */
$eventsManager = new EventsManager();

//Default Before Handle Request
$eventsManager->attach(
    'application:beforeHandleRequest',
    new \helper\listener()
);

//Product Event
$eventsManager->attach(
    'listener:addProduct',
    new \helper\listener()
);

//Order Event
$eventsManager->attach(
    'listener:addOrder',
    new \helper\listener()
);

/******************************Events End********************************** */


/**********************************Container Start********************************** */
$container = new FactoryDefault();

$container->set(
    'EventManager',
    $eventsManager
);

$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);

$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);


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
    
$container->set(
    'session',
    function () {
    $session = new Manager();
    $files = new Stream(
    [
        'savePath' => '/tmp',
        ]
    );
    $session->setAdapter($files);
    $session->start();
    return $session;
    }
);
    
$container->set(
    'objects',
    function () {
        $objects = array(
            'sanitize' => new \helper\sanitize(),
            'translate' => (new \helper\locale())->getTranslator()
        );
        return (object)$objects;
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
    
$container->set(
    'mongo',
    function () {
        $mongo = new MongoClient();
        
    return $mongo->selectDB('phalt');
    },
    true
);
/**********************************Container End********************************** */

$application = new Application($container);

$application->setEventsManager($eventsManager);

try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );
    
    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
