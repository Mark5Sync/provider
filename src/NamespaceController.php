<?php

namespace marksync\provider;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;

class NamespaceController
{
    private string $realRoot;

    function __construct(private string $root, private string $namespace)
    {
        $this->realRoot = realpath($root);
    }

    function handle(array &$mapGenerator)
    {
        foreach ($mapGenerator as $classNamespace => $classFile) {
            $ex = pathinfo($classFile);
            ['dirname' => $dirname, 'filename' => $className] = pathinfo($classFile);

            $realNamespace = $this->namespace . str_replace('/', '\\', str_replace($this->realRoot, '', $dirname));

            if ("$realNamespace\\$className" != $classNamespace) {
                $code = $this->processCodeWithNamespace(file_get_contents($classFile), $realNamespace);
                file_put_contents($classFile, $code);

                unset($mapGenerator[$classNamespace]);
                $mapGenerator["$realNamespace\\$className"] = $classFile;
            }
        }
    }


    private function processCodeWithNamespace($code, $newNamespace)
    {
        $find = false;

        $code = preg_replace_callback(
            "/namespace\s(.*);/",
            function ($reg) use (&$find, $newNamespace) {
                $find = true;

                return "namespace $newNamespace;";
            },
            $code
        );


        if (!$find)
            $code = str_replace('<?php', <<<PHP
            <?php
            namespace $newNamespace; // #autonamespace

            PHP, $code);

        return $code;
    }
}
