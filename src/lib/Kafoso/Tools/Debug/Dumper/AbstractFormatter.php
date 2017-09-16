<?php
namespace Kafoso\Tools\Debug\Dumper;

use Kafoso\Tools\Debug\VariableDumper;
use Ramsey\Uuid\Uuid;

abstract class AbstractFormatter
{
    const DEPTH_DEFAULT = 3;
    const PSR_2_SOFT_CHARACTER_LIMIT = 120;
    const PSR_2_INDENTATION_CHARACTERS = "    ";

    protected $var;
    protected $depth;

    private $uuid;

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
        $this->uuid = (string)Uuid::uuid1();
    }

    abstract public function render();

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    public static function generateTextIndentationForLevel($level)
    {
        return str_repeat(static::getIndentationCharacters(), $level);
    }

    public static function getIndentationCharacters()
    {
        return static::PSR_2_INDENTATION_CHARACTERS;
    }
}
