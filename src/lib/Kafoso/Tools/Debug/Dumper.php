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
    protected static $htmlFormatter = null;
    protected static $jsonFormatter = null;
    protected static $plainTextFormatter = null;

    /**
     * Dumps a plain text representation of all varies (Objects included) in the input variable.
     */
    public static function dump($var, $depth = 3)
    {
        echo(self::prepare($var, $depth));
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

    public static function dumpPre($var, $depth = 3)
    {
        echo("<pre>" . self::prepare($var, $depth) . "</pre>");
    }

    public static function prepare($var, $depth = 3)
    {
        $plainTextFormatter = new PlainTextFormatter(PlainTextFormatter\Configuration::createDefault());
        return self::getPlainTextformatter()->render($var, $depth);
    }

    public static function prepareHtml($var, $depth = 3)
    {
        return self::getHtmlFormatter()->render($var, $depth);
    }

    public static function prepareJson($var, $depth = 3, $prettyPrint = true)
    {
        $options = 0;
        if ($prettyPrint) {
            $options |= JSON_PRETTY_PRINT;
        }
        $jsonFormatter = self::getJsonFormatter();
        $jsonFormatter->getConfiguration()->setJsonOptions($options);
        return $jsonFormatter->render($var, $depth);
    }

    public static function getHtmlFormatter()
    {
        if (null === self::$htmlFormatter) {
            self::$htmlFormatter = new HtmlFormatter(HtmlFormatter\Configuration::createFromSuperglobalCookie());
        }
        return self::$htmlFormatter;
    }

    public static function getJsonFormatter()
    {
        if (null === self::$jsonFormatter) {
            self::$jsonFormatter = new JsonFormatter(JsonFormatter\Configuration::createDefault());
        }
        return self::$jsonFormatter;
    }

    public static function getPlainTextFormatter()
    {
        if (null === self::$plainTextFormatter) {
            self::$plainTextFormatter = new PlainTextFormatter(PlainTextFormatter\Configuration::createDefault());
        }
        return self::$plainTextFormatter;
    }
}
