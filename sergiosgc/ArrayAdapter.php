<?php
namespace sergiosgc;

class ArrayAdapter extends \ArrayObject {
    public static function from(array $data) : ArrayAdapter {
        if ($data instanceof ArrayAdapter) return $data;
        return new ArrayAdapter($data);
    }
    public function map(callable $callable) : ArrayAdapter {
        return self::from(array_map($callable, (array) $this, array_keys((array) $this)));
    }
    public function mapAssociative($callable) {
        return $this->map($callable)->makeAssociative();
    }
    public function makeAssociative() : ArrayAdapter {
        return self::from(array_reduce(
            (array) $this,
            function ($acc, $item) { $acc[$item[0]] = $item[1]; return $acc; },
            []
        ));
    }
    public function reduce(callable $callback, mixed $initial = null) : mixed {
        $result = array_reduce((array) $this, $callback, $initial);
        if (is_array($result)) return self::from($result);
        return $result;
    }
    public function reduceAssociative(callable $callback) : ArrayAdapter {
        return $this->map($callback)->filter()->makeAssociative();
    }
    public function filter(callable $callback = null) {
        return self::from(array_filter((array) $this, $callback ?? function($v) { return $v; }, ARRAY_FILTER_USE_BOTH));
    }
    public function implode(string $glue = '') : string {
        return implode((array) $this, $glue);
    }
    public static function explode(string $delimiter, string $str, nt $limit = PHP_INT_MAX) : ArrayAdapter {
        return static::from(explode($delimiter, $str, $limit));
    }
    public function values() : ArrayAdapter {
        return static::from(array_values((array) $this));
    }
    public function unique($strict = false) : ArrayAdapter {
        $haystack = (array) $this;
        return $this->filter(function ($v, $k) use ($strict, $haystack) { return $k === array_search($v, $haystack, $strict); });
    }
    public function zip(...$targets) {
        if (count($targets) == 0) return $this;
        foreach ($targets as $idx => $target) if (count($this) != count($target)) throw new Exception(sprintf('Cannot zip arrays of different sizes. Argument %d as size %d != %d', $idx, count($target), count($this)));
        foreach (array_keys($targets) as $idx) $targets[$idx] = array_values((array) $targets[$idx]);
        $result = new ArrayAdapter;
        for ($i=0; $i<count($this); $i++) {
            $elm = [ $this[$i] ];
            foreach ($targets as $idx => $target) $elm[] = $target[$i];
            $result[] = $elm;
        }
        return $result;
    }
    public function merge(...$targets) : ArrayAdapter {
        foreach (array_keys($targets) as $i) $targets[$i] = (array) $targets[$i];
        return static::from(array_merge((array) $this, ...$targets));
    }
    public function mergeAssociative(...$targets) : ArrayAdapter {
        foreach (array_keys($targets) as $i) $targets[$i] = (array) $targets[$i];
        $result = static::from((array) $this);
        foreach (array_keys($targets) as $target) foreach(array_keys($targets[$target]) as $key) $result[$key] = $targets[$target][$key];
        return $result;
    }
    public function toArray() : array {
        return (array) $this;
    }
}