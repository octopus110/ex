<?php

/**
 * common func, can be covered
 */
if (!function_exists('redis')) {
    function redis(string $name = null): \Slion\DB\Redis\Client {
        return \Slion\Redis::instance($name);
    }
}

