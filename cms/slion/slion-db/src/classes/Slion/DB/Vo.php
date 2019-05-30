<?php

namespace Slion\DB;
use Illuminate\Support\Collection;
use Slion\Meta;

/**
 * Description of Vo
 *
 * @author andares
 */
abstract class Vo extends Meta\Base implements \ArrayAccess, \Serializable, \JsonSerializable {
    use Meta\Access, Meta\Serializable, Meta\Json;

    protected static $_name = '';

    protected static $_index_field = '';

    protected static $_fields_mapping = [];

    protected static $_autoload = [
//        'vo_class' => ['id1', 'id2']
    ];

    protected static $_autoload_method = [];

    /**
     *
     * @param type $data
     */
    public function __construct($data = null) {
        $data && $this->fill((is_object($data) && method_exists($data, 'toArray')) ?
            $data->toArray() : $data);
    }

    /**
     *
     * @param type $data
     * @param type $excludes
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function fill($data, $excludes = []) {
        if (!is_array($data) && !is_object($data)) {
            throw new \InvalidArgumentException("fill data error");
        }

        $fields = $this->getDefault();
        if ($excludes) {
            foreach ($excludes as $name) {
                unset($fields[$name]);
            }
        }
        foreach ($fields as $name => $default) {
            // 只要有值就赋值
            isset($data[$name]) && $this->$name = $data[$name];

            // 映射赋值
            if (isset(static::$_fields_mapping[$name])) {
                $mapped = static::$_fields_mapping[$name];
                isset($data[$mapped]) && $this->$name = $data[$mapped];
            }
        }
        return $this;
    }

    /**
     * vo默认输出名。目前只用在autoload中。
     * @return string
     */
    public static function getName(): string {
        return static::$_name ?: static::class;
    }

    /**
     * 自动载入持扩展功能
     * @param array $ids
     * @return array
     */
    public static function autoloadHandler(Vo\Autoload $autoload,
        array $ids, int $method = 0): array {}

    /**
     *
     * @param int $offset
     * @param int $limit
     * @return \Slion\DB\Vo\Block
     */
    public static function makeBlock(int $offset, int $limit): Vo\Block {
        return new Vo\Block($offset, $limit, function($collection, ...$more) {
            return static::makeArray($collection, ...$more);
        });
    }

    /**
     *
     * @param Collection|array $collection
     * @return array
     */
    public static function makeArray($collection, ...$more): array {
        $result = [];
        foreach (static::unionData($collection, ...$more) as $vo) {
            $result[] = $vo->toArray();
        }
        return $result;
    }

    /**
     *
     * @param type $collection
     * @param type $more
     * @return type
     */
    public static function makeIndexedArray($collection, ...$more) {
        $result = [];
        foreach (static::unionData($collection, ...$more) as $vo) {
            $row = $vo->toArray();
            if (static::$_index_field) {
                $result[$row[static::$_index_field]] = $row;
            } else {
                $result[] = $row;
            }
        }
        return $result;
    }

    /**
     * 这里有一个较为hack的实现，即$more的末个参数将被检查是否是autoload对象？
     * 是的话将进行add操作。
     *
     * @param type $collection
     * @param type $more
     */
    protected static function unionData($collection, ...$more) {
        $autoload = array_pop($more);
        if (!($autoload instanceof Vo\Autoload)) {
            $autoload = null;
        }

        $more_data  = [];
        foreach ($collection as $key => $row) {
            // 多维填充
            if ($more) {
                $more_data = [];
                foreach ($more as $more_collection) {
                    $more_data[] = $more_collection[$key] ?? null;
                }
            }
            $vo = new static($row, ...$more_data);
            /* @var $vo self */
            $vo->confirm();
            $autoload && $vo->addAutoloadIds($autoload);
            yield $vo;
        }
    }

    /**
     *
     * @param self $this
     */
    public function addAutoloadIds(Vo\Autoload $autoload) {
        $binds = $autoload->getBindsByMasterClass(static::class);
        if (!$binds) {
            return false;
        }

        foreach ($binds as $class) {
            if (!isset(static::$_autoload[$class])) {
                continue;
            }
            $id_field   = static::$_autoload[$class];
            $method     = static::$_autoload_method[$class] ?? 0;

            if (is_array($id_field)) {
                // 支持二层数组
                // 二层数组时表示该vo中某个autoload vo class要载入多个
                if (is_array($id_field[0])) {
                    foreach ($id_field as $id_field_more) {
                        $ids = [];
                        foreach ($id_field_more as $field) {
                            if ($this->$field === null ||
                                $autoload->Unmasked(static::class, $field)) {

                                continue 2; // 防止填null
                            }
                            $ids[] = $this->$field;
                        }
                        $autoload->add($class, $method, ...$ids);
                    }
                } else {
                    $ids = [];
                    foreach ($id_field as $field) {
                        if ($autoload->Unmasked(static::class, $field)) {
                            $ids = [];
                            break;
                        }
                        $ids[] = $this->$field;
                    }
                    $ids && $autoload->add($class, $method, ...$ids);
                }
            } else {
                !$autoload->Unmasked(static::class, $id_field) &&
                    $autoload->add($class, $method, $this->$id_field);
            }
        }
        return true;
    }
}
