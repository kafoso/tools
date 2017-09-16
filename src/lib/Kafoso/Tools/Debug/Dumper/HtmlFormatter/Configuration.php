<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter;

class Configuration
{
    const DEFAULT_MAXIMUM_LEVEL = 2;

    private $maximumLevel = self::DEFAULT_MAXIMUM_LEVEL;
    private $isTruncatingGenericObjects = true;
    private $omittedClassNames = [];

    /**
     * @param int $maximumLevel                 An unsigned integer.
     * @param bool $isTruncatingGenericObjects
     */
    public function __construct($maximumLevel, $isTruncatingGenericObjects = true)
    {
        $this->maximumLevel = $maximumLevel;
        $this->isTruncatingGenericObjects = $isTruncatingGenericObjects;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function addOmittedClass($className)
    {
        if (false == in_array($className, $this->omittedClassNames)) {
            $this->omittedClassNames[] = $className;
        }
        return $this;
    }

    /**
     * @param array $classNames                 An array of strings.
     * @return $this
     */
    public function addOmittedClasses(array $classNames)
    {
        foreach ($classNames as $className) {
            $this->addOmittedClass($className);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getOmittedClassNames()
    {
        return $this->omittedClassNames;
    }

    /**
     * @return int
     */
    public function getMaximumLevel()
    {
        return $this->maximumLevel;
    }

    /**
     * @return array
     */
    public function getTruncatedGenericClasses()
    {
        return [
            "Closure",
            "DateInterval",
            "DatePeriod",
            "DateTime",
            "DateTimeImmutable",
        ];
    }

    /**
     * @return bool
     */
    public function isTruncatingGenericObjects()
    {
        return $this->isTruncatingGenericObjects;
    }

    public static function createFromSuperglobelCookie()
    {
        $isTruncatingGenericObjects = true;
        if (isset($_COOKIE, $_COOKIE[HtmlFormatter::COOKIE_NAME])) {
            $options = @json_decode($_COOKIE[HtmlFormatter::COOKIE_NAME], true) ?: null;
            if ($options) {
                if (isset($options['isTruncatingGenericObjects'])
                    && in_array(strval($options['isTruncatingGenericObjects']), ["0", "1"])) {
                    $isTruncatingGenericObjects = boolval(intval($options['isTruncatingGenericObjects']));
                }
            }
        }
        return new self(self::DEFAULT_MAXIMUM_LEVEL, $isTruncatingGenericObjects);
    }
}
