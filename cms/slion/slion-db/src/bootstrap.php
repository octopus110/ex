<?php
namespace Slion;
use Slim\{App, Container};

$run = $GLOBALS['run'];
/* @var $run \Slion\Run */
// setup自身
$run->add('slion-db', __DIR__)

    ->setup(60, function(string $root) {
        $app        = $this->app();
        $container  = $this->container();
        $settings   = $this->settings();

        require "$root/dependencies.php";
        require "$root/helpers.php";
        require "$root/hooks.php";
    }, 'load dependencies')
;