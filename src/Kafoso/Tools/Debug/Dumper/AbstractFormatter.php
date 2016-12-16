<?php
namespace Kafoso\Tools\Debug\Dumper;

use Kafoso\Tools\Debug\VariableDumper;

abstract class AbstractFormatter
{
    const DEPTH_DEFAULT = 3;
    const INDENTATION_CHARACTERS = "    ";

    protected $var;
    protected $depth;

    public function __construct($var, $depth = null)
    {
        $this->var = $var;
        if (is_null($depth)) {
            $depth = self::DEPTH_DEFAULT;
        }
        if (false == is_int($depth) || $depth <= 0 ) {
            throw new \RuntimeException(sprintf(
                "Expects parameter \"\$depth\" to be an integer [0;∞]. Found: %s",
                VariableDumper::found($depth)
            ));
        }
        $this->depth = $depth;
    }
}
