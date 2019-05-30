<?php

namespace Slion;
use \Illuminate\Database\Capsule\Manager;

/**
 * Description of DB
 *
 * @author andares
 *
 * @method void beginTransaction()
 * @method void rollBack()
 * @method void commit()
 */
class DB {
    /**
     *
     * @var \Slim\Container
     */
    private $container;

    /**
     *
     * @var Manager
     */
    private $capsule;

    public function __construct(\Slim\Container $container, array $connections_conf = []) {
        $this->container = $container;

        //创建Eloquent
        $capsule = new Manager;
        foreach ($connections_conf as $name => $conf) {
            $capsule->addConnection($conf, $name);
        }
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        $this->capsule = $capsule;
    }

    public function __call(string $name, $arguments) {
        $this->capsule->$name(...$arguments);
    }

    public static function __callStatic($method, $parameters) {
        try {
            return Manager::$method(...$parameters);
        } catch (\Throwable $exc) {
            if ($exc->getMessage() !=
                'Call to a member function getConnection() on null') {
                throw $exc;
            }
        }
    }
}
