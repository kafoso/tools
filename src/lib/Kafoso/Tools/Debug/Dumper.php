<?php
namespace Kafoso\Tools\Debug;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter;
use Kafoso\Tools\Debug\Dumper\JsonFormatter;
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
    public static function dump($var, $depth = 3, $isTruncatingRecursion = true)
    {
        echo(self::prepare($var, $depth, $isTruncatingRecursion));
    }

    /**
     * Dumps the input variable in a HTML format with styling and minimal Javascript functionality to expand child
     * arrays and objects. Recursive truncation is enforced to avoid the HTML tree from expanding endlessly.
     */
    public static function dumpHtml($var, $depth = 3)
    {
        echo(self::prepareHtml($var, $depth));
    }

    /**
     * Dumps the input variable in a JSON format. Recursive truncation is enforced, because this is a means of working
     * around the json_encode error "recursion detected".
     */
    public static function dumpJson($var, $depth = 3, $prettyPrint = true)
    {
        echo(self::prepareJson($var, $depth, $prettyPrint));
    }

    public static function dumpPre($var, $depth = 3, $isTruncatingRecursion = true)
    {
        echo("<pre>" . self::prepare($var, $depth, $isTruncatingRecursion) . "</pre>");
    }

    public static function prepare($var, $depth = 3, $isTruncatingRecursion = true)
    {
        $plainTextFormatter = new PlainTextFormatter($var, $depth, $isTruncatingRecursion);
        return $plainTextFormatter->render();
    }

    public static function prepareHtml($var, $depth = 3)
    {
        $htmlFormatter = new HtmlFormatter($var, $depth);
        return $htmlFormatter->render();
    }

    public static function prepareJson($var, $depth = 3, $prettyPrint = true)
    {
        $options = 0;
        if ($prettyPrint) {
            $options |= JSON_PRETTY_PRINT;
        }
        $jsonFormatter = new JsonFormatter($var, $depth, $options);
        return $jsonFormatter->render();
    }
}
