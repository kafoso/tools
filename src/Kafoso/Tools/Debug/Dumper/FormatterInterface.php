<?php
namespace Kafoso\Tools\Debug\Dumper;

interface FormatterInterface
{
    public static function prepareRecursively($var, $depth, $isTrucatingRecursion, $level,
        array $previousSplObjectHashes);
}
