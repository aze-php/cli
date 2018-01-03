<?php

namespace AZE\Init;

use AZE\core\configuration\Config;
use AZE\core\Debug;
use AZE\core\routing\Router;
use AZE\log\Logger;

class Init implements \AZE\core\InitializerInterface
{

    public static function initialize()
    {
        Config::get()->loadJson(__DIR__ . '/config/config.json');

        if (Config::get()->debug) {
            Debug::activated(true);
            Logger::dump(Config::get());
        }

        Router::setRoutes(__DIR__ . '/routing.xml');
        Router::setRoutingType(Router::DYNAMIC);
    }

}