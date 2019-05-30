<?php
namespace Slion\DB\Vo;

/**
 * Description of Autoload
 *
 * @author andares
 */
trait Aggregation {
    abstract protected static function base(): array;
    abstract protected static function bind(Autoload $autoload);

    /**
     * @param \Slim\Collection $loads
     * @return self|null
     */
    abstract protected function pull(\Slim\Collection $loads);

    protected static function unionData($collection, ...$more) {
        $autoload = array_pop($more);
        if (!($autoload instanceof Autoload)) {
            $autoload && $more[] = $autoload; // 防null
            $autoload = new Autoload();
        }
        static::bind($autoload);

        $base       = static::base();
        $temp       = [];
        $more_data  = [];
        foreach ($collection as $key => $row) {
            // 多维填充
            if ($more) {
                $more_data = [];
                foreach ($more as $more_collection) {
                    $more_data[] = $more_collection[$key] ?? null;
                }
            }
            $vo = new static();
            foreach ($base as $base_field => $base_class) {
                $base_vo = new $base_class($row, ...$more_data);
                $autoload && $base_vo->addAutoloadIds($autoload);

                if (is_string($base_field)) {
                    $vo->$base_field = $base_vo;
                } else {
                    $vo->fill($base_vo);
                }
            }
            /* @var $vo self */
            $temp[] = $vo;
        }

        $loads = $autoload();
        foreach ($temp as $vo) {
            if ($vo->pull($loads)) {
                $vo->confirm();
                yield $vo;
            }
        }
    }

}
