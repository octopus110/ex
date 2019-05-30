<?php
namespace Slion\DB\Vo;

use Slion\Http\{Response};
use Slim\Collection;

/**
 * Description of Autoload
 *
        $this->db_autoload
            ->bind(Vo\Follow::class,    Vo\UserOther::class)
            ->bind(Vo\UserOther::class, Vo\Room::class)
            ->bind(Vo\Room::class,      Vo\Vdoid::class)
            ->bind(Vo\Vdoid::class,     Vo\Column::class);
        $response->follow_list = Vo\Follow::makeArray(
            Models\Follow::getFollowList($user->getId()),
            $this->db_autoload);
 *
 * @author andares
 */
class Autoload {

    /**
     * 在一个autoload中绑定自动载入的对应关系
     * @var array
     */
    private $binds = [];

    /**
     *
     * @var array
     */
    private $masked = [];

    /**
     *
     * @var array
     */
    private $ids = [];

    /**
     *
     * @var array
     */
    private $loaded_ids = [];

    public function bind(string $master_vo_class, ...$bind_vo_classes): self {
        $this->binds[$master_vo_class] = $bind_vo_classes;
        return $this;
    }

    public function mask(string $vo_class, ...$mask_fields):self {
        $this->masked[$vo_class] = [];
        foreach ($mask_fields as $field) {
            $this->masked[$vo_class][$field] = 1;
        }
        return $this;
    }

    public function Unmasked(string $vo_class, string $field): bool {
        return isset($this->masked[$vo_class]) &&
            !isset($this->masked[$vo_class][$field]);
    }

    public function unbind(string $master_vo_class): self {
        unset($this->binds[$master_vo_class]);
        unset($this->masked[$master_vo_class]);
        return $this;
    }

    public function resetBinds(): self {
        $this->binds = [];
        return $this;
    }

    public function getBindsByMasterClass(string $master_vo_class): array {
        return $this->binds[$master_vo_class] ?? [];
    }

    public function add(string $class, int $method, ...$ids) {
        !isset($this->ids[$class][$method]) && $this->ids[$class][$method] = [];
        $this->ids[$class][$method] = array_unique(
            array_merge($this->ids[$class][$method],
                count($ids) > 1 ? [$ids] : $ids),
            \SORT_REGULAR);
    }

    public function __invoke() {
        $loads = new Collection;
        do {
            list($class, $method, $ids) = $this->fetchIds();
            if ($class && $ids) {
                $name = $class::getName();
                if (isset($loads[$name])) {
                    $appender = function(array $list) use ($name) {
                        foreach ($list as $id => $row) {
                            if (isset($this->data[$name][$id])) {
                                continue;
                            }
                            $this->data[$name][$id] = $row;
                        }
                    };
                    $appender->call($loads,
                        $class::autoloadHandler($this, $ids, $method));
                } else {
                    $loads[$name] = $class::autoloadHandler($this,
                        $ids, $method);
                }
            }
        } while($class);

        return $loads;
    }

    /**
     * 取出当前要自动载入的vo的所有ids列表及读取方法
     * @return array
     */
    private function fetchIds(): array {
        $class = key($this->ids);
        if (!$class) {
            return [null, null, null];
        }

        // 这里默认method层与class同步
        $method = key($this->ids[$class]);
        $ids    = $this->getIdsWithoutLoaded($class, $method,
            $this->ids[$class][$method]);
        unset($this->ids[$class][$method]);
        if (!$this->ids[$class]) {
            unset($this->ids[$class]);
        }

        return [$class, $method, $ids];
    }

    /**
     * 防重复。
     *
     * @todo 在应用了cabin后再考虑是否移除。
     * @param string $class
     * @param array $ids
     * @return array
     */
    private function getIdsWithoutLoaded(string $class,
        int $method, array $ids): array {

        if (isset($this->loaded_ids[$class][$method])) {
            $ids = array_diff($ids, $this->loaded_ids[$class][$method]);
            $this->loaded_ids[$class][$method] = array_merge(
                $this->loaded_ids[$class][$method], $ids);
            return $ids;
        }

        $this->loaded_ids[$class][$method] = $ids;
        return $ids;
    }
}
