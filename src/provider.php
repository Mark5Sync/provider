<?php

namespace marksync\provider;

trait provider
{
    private $___binding___ = [];

    /**
     * @xdebug_never
     */
    public function __get($alias)
    {
        if (!isset($this->___binding___[$alias]))
            $this->___binding___[$alias] = $this->__getAlias__($alias);

        return $this->___binding___[$alias];
    }


    private function __getAlias__(string $alias)
    {
        if ($object = $this->__checkAliasPrefix__('create' . ucfirst($alias)))
            return $object;

        if ($object = $this->__checkAliasPrefix__($alias))
            return $object;

        else
            if (method_exists($this, '___get'))
                return $this->___get($alias);
    }


    private function __checkAliasPrefix__(string $frefixAlias)
    {
        if (method_exists($this, "_{$frefixAlias}"))
            return $this->{"_{$frefixAlias}"}();

        if (method_exists($this, $frefixAlias))
            if (Container::isset($frefixAlias))
                return Container::get($frefixAlias);
            else
                return Container::set($frefixAlias, $this->{$frefixAlias}());
    }


    private function super(string $alias)
    {
        return fn ($class) => Container::set($alias, $class);
    }
}
