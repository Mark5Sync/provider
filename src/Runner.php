<?php

namespace marksync\provider;

use Composer\ClassMapGenerator\ClassMapGenerator;




class Runner
{
    private $out;
    private MarkerTraitBuilder $builder;

    function __construct($dir, $psr4, string $markersFolder = 'markers')
    {
        $this->out = $markersFolder;
        $this->builder = new MarkerTraitBuilder;

        foreach ($psr4 as $namespace => $folder) {
            if ($namespace=='markdi\\')
                continue;

            $this->handleMap($dir . "/$folder");
        }
    }



    private function handleMap($src)
    {
        $this->clearOutput($src);
        $map = ClassMapGenerator::createMap($src);
        $this->createEmptyMarkers($src, $map);

        $group = [];
        foreach ($map as $class => $file) {
            $mark = new ReflectionMark($class);
            if (!$mark->exception)
                $group[$mark->markerClass][] = $mark;
        }

        if (empty($group))
            return;

        foreach ($group as $markers) {
            $this->builder->create($src, $this->out, $markers);
        }
    }


    private function createEmptyMarkers($src, array $map){
        if (!file_exists("$src/_$this->out"))
            mkdir("$src/_$this->out", 0777, true);

        $added = [];

        foreach ($map as $class => $file) {
            [$main, $marker, $extra] = explode('\\', "$class\\@");
            if ($extra == '@')
                $marker = 'main';

            if (isset($added[$marker]) || str_starts_with($marker, '_'))
                continue;

            $code = <<<PHP
            <?php namespace $main\\_$this->out;
            trait $marker{}
            PHP;

            file_put_contents("$src/_$this->out/$marker.php", $code);

            $added[$marker] = true;
        }
    }


    private function clearOutput($path)
    {
        if (file_exists("$path/_$this->out")) {
            $this->removeFolder("$path/_$this->out");
        }
    }

    private function removeFolder(string $path)
    {
        if (PHP_OS === 'Windows') {
            exec(sprintf("rd /s /q %s", escapeshellarg($path)));
        } else {
            exec(sprintf("rm -rf %s", escapeshellarg($path)));
        }
    }
}
