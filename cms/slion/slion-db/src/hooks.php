<?php
namespace Slion;

$hook = $container->get('hook');
/* @var $hook \Slion\Hook */
$hook->attach(HOOK_BEFORE_RESPONSE, function(Run $run,
    Http\Response $response, ...$args) {

    $autoload = $run->db_autoload;
    $response->setChannelData('autoload', $autoload());
});
