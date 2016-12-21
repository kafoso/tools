<?php
namespace Kafoso\Tools\Debug\Dumper;

use Kafoso\Tools\Debug\VariableDumper;

abstract class AbstractFormatter
{
    const DEPTH_DEFAULT = 3;
    const INDENTATION_CHARACTER = " ";
    const INDENTATION_CHARACTER_COUNT = 4;

    protected $var;
    protected $depth;

    private $indentationCharacters;

    public function __construct($var, $depth = null)
    {
        $this->var = $var;
        if (is_null($depth)) {
            $depth = static::DEPTH_DEFAULT;
        }
        if (false == is_int($depth) || $depth <= 0 ) {
            throw new \RuntimeException(sprintf(
                "Expects parameter \"\$depth\" to be an integer [0;∞]. Found: %s",
                VariableDumper::found($depth)
            ));
        }
        $this->depth = $depth;
    }

    abstract public function render();

    public function getIndentationCharacters()
    {
        if (!$this->indentationCharacters) {
            $this->indentationCharacters = str_repeat(
                static::INDENTATION_CHARACTER,
                static::INDENTATION_CHARACTER_COUNT
            );
        }
        return $this->indentationCharacters;
    }
}
