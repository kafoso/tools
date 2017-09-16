<?php
namespace Kafoso\Tools\Generic;

use Kafoso\Tools\Exception\Formatter;

class HTML
{
    private static $_instance;

    private $encoding;
    private $htmlspecialcharsFlags;

    /**
     * @param string $encoding
     * @param null|int $htmlspecialcharsFlags
     */
    public function __construct($encoding, $htmlspecialcharsFlags)
    {
        $this->encoding = $encoding;
        $this->htmlspecialcharsFlags = $htmlspecialcharsFlags;
    }

    public function htmlspecialchars($str)
    {
        return htmlspecialchars($str, $this->htmlspecialcharsFlags, $this->encoding);
    }

    public function htmlspecialcharsSprintf()
    {
        $instance = $this;
        return $this->_htmlspecialcharsSprintf(func_get_args(), function($segment) use ($instance){
            return $instance->htmlspecialchars($segment);
        });
    }

    /**
     * Encodes HTML characters and enforces encoding.
     */
    public static function encode($str)
    {
        return self::getInstance()->htmlspecialchars($str);
    }

    /**
     * Same usage as sprintf, but allows each match to contain HTML, e.g. encodeSprintf("foo & %s", '<p>bar</p>') will
     * produce
     *     foo &amp; <p>bar</p>
     * , where "foo &amp; " is HTML escaped, but '<p>bar</p>' is not. Therefore, the text "bar" should also be escaped.
     * @return string
     */
    public static function encodeSprintf()
    {
        return call_user_func_array([self::getInstance(), 'htmlspecialcharsSprintf'], func_get_args());
    }

    /**
     * @return void
     */
    public static function setInstance(HTML $html)
    {
        self::$_instance = $html;
    }

    /**
     * @return \Kafoso\Tools\Generic\HTML
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self('UTF-8', ENT_COMPAT | ENT_HTML5);
        }
        return self::$_instance;
    }

    private static function _htmlspecialcharsSprintf($args, callable $callback)
    {
        if (!isset($args[0]) || false == is_string($args[0])) {
            throw new \InvalidArgumentException(sprintf(
                "Expects paramter #1 to be a string. Found: %s",
                Formatter::found($args[0])
            ));
        }
        $str = $args[0];
        $split = self::_pregSplitSprintfString($str);
        foreach ($split as $i => &$segment) {
            if (0 == ($i%2)) {
                $segment = $callback($segment);
            }
        }
        $str = implode("", $split);
        $replacements = array_slice($args, 1);
        return vsprintf($str, $replacements);
    }

    private static function _pregSplitSprintfString($str)
    {
        return preg_split('/(%[bcdeEfFgGosuxX]|%\d+\$[bcdeEfFgGosuxX]|%\+d)/', $str, -1, PREG_SPLIT_DELIM_CAPTURE);
    }
}
