<?php
namespace Kafoso\Tools\Debug\Dumper;

abstract class AbstractConfiguration
{
    const DEFAULT_COLLAPSE_LEVEL = 2;
    const DEFAULT_DEPTH_LEVEL = 3;
    const MAXIMUM_LEVEL = 8;

    protected $collapseLevel = self::DEFAULT_COLLAPSE_LEVEL;
    protected $suppressedClassNames = [];
    protected $depth = self::DEFAULT_DEPTH_LEVEL;
    protected $isTruncatingGenericObjects = true;

    protected function __construct(){}

    /**
     * @param string $className
     * @return $this
     */
    public function addSuppressedClass($className)
    {
        if (false == in_array($className, $this->suppressedClassNames)) {
            $this->suppressedClassNames[] = $className;
        }
        return $this;
    }

    /**
     * @param array $classNames                 An array of strings.
     * @return $this
     */
    public function addSuppressedClasses(array $classNames)
    {
        foreach ($classNames as $className) {
            if (is_string($className)) {
                $this->addSuppressedClass($className);
            }
        }
        return $this;
    }

    /**
     * @param int $depth
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @return $this
     */
    public function setDepth($depth)
    {
        if (false == is_int($depth)) {
            throw new \InvalidArgumentException(sprintf(
                "Expects argument \$depth to be in integer. Found: %s",
                Formatter::found($depth)
            ));
        }
        if ($depth < 1 || $depth > self::MAXIMUM_LEVEL) {
            throw new \UnexpectedValueException(sprintf(
                "Expects argument \$depth to be in integer between %d and %d. Found: %s",
                1,
                self::MAXIMUM_LEVEL,
                Formatter::found($depth)
            ));
        }
        $this->depth = $depth;
        return $this;
    }

    /**
     * @return int
     */
    public function getCollapseLevel()
    {
        return $this->collapseLevel;
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @return array
     */
    public function getSuppressedClassNames()
    {
        return $this->suppressedClassNames;
    }

    /**
     * @return bool
     */
    public function isTruncatingGenericObjects()
    {
        return $this->isTruncatingGenericObjects;
    }

    /**
     * @return Configuration
     */
    public static function createDefault()
    {
        return new static;
    }

    /**
     * @return array
     */
    public static function getTruncatedGenericClasses()
    {
        return [
            "Closure",
            "DateInterval",
            "DatePeriod",
            "DateTime",
            "DateTimeImmutable",
        ];
    }
}
