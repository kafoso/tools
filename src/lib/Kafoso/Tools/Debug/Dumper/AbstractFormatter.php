<?php
namespace Kafoso\Tools\Debug\Dumper;

use Kafoso\Tools\Debug\VariableDumper;
use Ramsey\Uuid\Uuid;

abstract class AbstractFormatter
{
    const DEPTH_DEFAULT = 3;
    const PSR_2_SOFT_CHARACTER_LIMIT = 120;
    const PSR_2_INDENTATION_CHARACTERS = "    ";

    protected $configuration;

    private $uuid;

    /**
     * @param mixed $var
     * @param null|int $depth
     */
    public function __construct(AbstractConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->uuid = (string)Uuid::uuid1();
    }

    /**
     * @param mixed $var
     * @param null|int $depth
     * @return string
     */
    abstract public function render($var, $depth = null);

    /**
     * @return AbstractConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Looks back through the debug_backtrace to determine from where the output originated.
     * @return null|array
     */
    public function getOrigin()
    {
        $calledFrom = null;
        $rootDirectory = realpath(__DIR__ . str_repeat("/..", 5));
        foreach (debug_backtrace() as $v) {
            if (0 === stripos($v['file'], $rootDirectory)) {
                continue;
            }
            $calledFrom = $v;
            break;
        }
        return $calledFrom;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public static function getIndentationCharacters()
    {
        return static::PSR_2_INDENTATION_CHARACTERS;
    }
}
