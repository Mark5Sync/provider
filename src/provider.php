<?php

namespace marksync\provider;

trait provider
{
    private $___binding___ = [];

    /**
     * @xdebug_never
     */
    public function __get($alias){
        if (!isset($this->___binding___[$alias]))
            $this->___binding___[$alias] = $this->__getAlias__($alias);

        return $this->___binding___[$alias];
    }


    private function __getAlias__(string $alias)
    {
        if (method_exists($this, "_$alias"))
            return $this->{"_$alias"}();

        if (method_exists($this, $alias))
            if (Container::isset($alias))
                return Container::get($alias);
            else
                return Container::set($alias, $this->$alias());
        else
            if (method_exists($this, '___get')) 
                return $this->___get($alias);
    }


    private function super(string $alias){
        return fn($class) => Container::set($alias, $class);
    }

}
