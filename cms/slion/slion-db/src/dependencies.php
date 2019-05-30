<?php
// DIC configuration
namespace Slion\DB;

// Cookies
$container['db_autoload'] = function(\Slim\Container $c) {
    return new Vo\Autoload();
};
