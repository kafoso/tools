<?php
namespace Kafoso\Tools\Debug;

use Kafoso\Tools\Debug\Dumper\PlainTextFormatter;

/**
 * A class for dumping debugging information in a human readable format without exhausting memory or causing hanging.
 * This is achieved by enforcing a maximum depth and by short-circuiting repeated objects in the structure (recursion).
 */
class Dumper
{
    /**
     * Dumps a plain text representation of all varies (Objects included) in the input variable.
     */
    public static function dump($var, $depth = 3, $isTrucatingRecursion = true)
    {
        echo(self::prepare($var, $depth, $isTrucatingRecursion));
    }

    /**
     * Dumps the input variable in a JSON format. Recursive truncation is enforced, because this is a means of working
     * around the json_encode error "recursion detected".
     */
    public static function dumpJson($var, $depth = 3)
    {
        echo(self::prepareJson($var, $depth));
    }

    public static function dumpPre($var, $depth = 3, $isTrucatingRecursion = true)
    {
        echo("<pre>" . self::prepare($var, $depth, $isTrucatingRecursion) . "</pre>");
    }

    public static function prepare($var, $depth = 3, $isTrucatingRecursion = true)
    {
        return PlainTextFormatter::prepareRecursively($var, $depth, $isTrucatingRecursion, 0, []);
    }

    public static function prepareJson($var, $depth = 3)
    {
        return JsonFormatter::prepareRecursively($var, $depth, true, 0, []);
    }
}
