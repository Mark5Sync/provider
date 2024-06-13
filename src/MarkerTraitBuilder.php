<?php

namespace marksync\provider;


class MarkerTraitBuilder
{

    function create($src, $markerDir, array $markers)
    {
        $namespaces = "";
        $varibles = "";
        $methods = "";

        foreach ($markers as $marker) {
            if (in_array($marker->marker, ['markdi', 'provider']))
                continue;

            $this->toCode(
                $marker,
                $props,
                $mehodProps,
                $namespaces,
                $varibles,
                $modeSymbol,
                $methods
            );
        }


        $code = <<<CODE
                <?php
                namespace {$marker->markerNamespace};
                use marksync\provider\provider;
                $namespaces
                /**
                $varibles
                */
                trait {$marker->marker} {
                    use provider;

                $methods
                }
                CODE;


        $this->checkFolder("{$src}/_{$markerDir}");

        file_put_contents("{$src}/_{$markerDir}/{$marker->marker}.php", $code);
    }


    private function checkFolder($folder)
    {
        if (!file_exists($folder))
            mkdir($folder, 0777, true);
    }

    private function toCode(
        ReflectionMark $marker,
        &$props,
        &$mehodProps,
        &$namespaces,
        &$varibles,
        &$modeSymbol,
        &$methods,
    ) {

        $prop = 'create' . ucfirst($marker->prop);
        $props = $this->getProps($marker->args, $prop, $marker->mode);
        $namespaces .= "use $marker->className;\n";

        if ($marker->mode != Mark::INSTANCE)
            $varibles   .= " * @property-read $marker->shortName \${$marker->prop}\n";

        $mehodProps = $marker->mode == Mark::INSTANCE
            ? $this->wrap(array_keys($marker->args), true)
            : '()';

        $modeSymbol = $marker->mode == Mark::LOCAL    ? '_'    : '';
        
        $methods   .= "   function {$modeSymbol}{$prop}$mehodProps: {$marker->shortName} { return new {$marker->shortName}$props; }\n";
    }


    private function getProps(array $args, string $title, int $mode)
    {
        if ($mode == Mark::INSTANCE)
            return $this->wrap($args);

        $result = [];

        foreach ($args as $argument) {
            switch ($argument) {
                case 'parent':
                    $result[] = '$this';
                    break;

                case 'super':
                    $result[] = "\$this->super('$title')";
                    break;

                default:
                    $result[] = $argument;
            }
        }

        return $this->wrap($result);
    }


    private function wrap(array $props, $forceWrap = false)
    {
        $resultStr = implode(', ', $props);
        return $resultStr || $forceWrap ? "($resultStr)" : '';
    }
}
