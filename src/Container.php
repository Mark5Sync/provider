<?php

namespace marksync\provider;


final class Container
{

    static $namespace = 'default';
    static $list = [];

    static function take(string $class)
    {
        if (static::isset($class))
            return static::get($class);

        return static::set($class, new $class);
    }

    static function isset($alias)
    {
        return isset(static::$list[static::$namespace][$alias]);
    }

    static function get($alias)
    {
        if (isset(static::$list[static::$namespace][$alias]))
            return static::$list[static::$namespace][$alias];

        $mark = new ReflectionMark($alias);
        $component = new $alias;

        static::$list[static::$namespace][$mark->prop] = &$component;
        static::$list[static::$namespace][$alias] = &$component;

        return $component;
    }

    static function set($alias, $component)
    {
        if (!isset(static::$list[static::$namespace]))
            static::$list[static::$namespace] = [];

        static::$list[static::$namespace][$alias] = &$component;
        static::$list[static::$namespace][get_class($component)] = &$component;

        return $component;
    }

    static function reset()
    {
        static::$list = [];
    }

    static function resetNamespace()
    {
        static::$list[static::$namespace] = [];
    }

    static function setNamespace($name = 'default')
    {
        static::$namespace = $name;
    }

    static function runNamespace(string $name, callable $run)
    {
        $old = static::$namespace;

        static::setNamespace($name);
        $run();
        static::setNamespace($old);
        unset(static::$list[$name]);
    }
}
