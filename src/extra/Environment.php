<?php

namespace marksync\provider\extra;


use Dotenv\Dotenv;

abstract class Environment
{
    public $root;
    private $props;

    final function __construct(){
        $this->root = $this->findEnvPath(__DIR__);
        $dotenv = Dotenv::createImmutable($this->root);
        $dotenv->load();

        $this->props = $_ENV;
    }

    final function __get($prop){
        $upperProp = strtoupper($prop);

        if (!isset($this->props[$upperProp]))
            return null;

        return $this->handle($upperProp, $this->props[$upperProp]);
    }


    protected function handle($key, $value){
        return $value;
    }


    final function findEnvPath($path)
    {
        if (function_exists('findComposerJson')){
            $root = findComposerJson($GLOBALS['_composer_bin_dir']);
            
            if (!$root)
                throw new \Exception('project dir not found', 1);
            
            $path = $root;
        }


        while (!file_exists($path . '/.env')) {
            $path = dirname($path, 1);

            if ($path == '/')
                throw new \Exception(".env файл отсутствует", 1);
                
        }
        return $path;
    }

}
