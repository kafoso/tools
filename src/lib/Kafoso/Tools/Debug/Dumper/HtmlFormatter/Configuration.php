<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter;

class Configuration
{
    const DEFAULT_COLLAPSE_LEVEL = 2;
    const MAXIMUM_LEVEL = 8;

    private $collapseLevel = self::DEFAULT_COLLAPSE_LEVEL;
    private $areOptionsShown = true;
    private $isShowingConstants = true;
    private $isShowingInterfaces = true;
    private $isShowingMethodParameters = true;
    private $isShowingMethodParameterTypeHints = true;
    private $isShowingMethods = true;
    private $isShowingParentClass = true;
    private $isShowingTraits = true;
    private $isShowingVariables = true;
    private $isTruncatingGenericObjects = true;
    private $suppressedClassNames = [];

    protected function __construct()
    {

    }

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
     * @return array
     */
    public function getSuppressedClassNames()
    {
        return $this->suppressedClassNames;
    }

    /**
     * @return int
     */
    public function getCollapseLevel()
    {
        return $this->collapseLevel;
    }

    /**
     * @return bool
     */
    public function areOptionsShown()
    {
        return $this->areOptionsShown;
    }

    /**
     * @return bool
     */
    public function isShowingConstants()
    {
        return $this->isShowingConstants;
    }

    /**
     * @return bool
     */
    public function isShowingInterfaces()
    {
        return $this->isShowingInterfaces;
    }

    /**
     * @return bool
     */
    public function isShowingMethodParameters()
    {
        return $this->isShowingMethodParameters;
    }

    /**
     * @return bool
     */
    public function isShowingMethodParameterTypeHints()
    {
        return $this->isShowingMethodParameterTypeHints;
    }

    /**
     * @return bool
     */
    public function isShowingMethods()
    {
        return $this->isShowingMethods;
    }

    /**
     * @return bool
     */
    public function isShowingParentClass()
    {
        return $this->isShowingParentClass;
    }

    /**
     * @return bool
     */
    public function isShowingTraits()
    {
        return $this->isShowingTraits;
    }

    /**
     * @return bool
     */
    public function isShowingVariables()
    {
        return $this->isShowingVariables;
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
    public static function createFromOptionsArray(array $options)
    {
        $configuration = new self(self::DEFAULT_COLLAPSE_LEVEL);
        foreach (self::getIntegerVariables() as $integerVariable) {
            if (isset($options[$integerVariable])) {
                $configuration->$integerVariable = intval($options[$integerVariable]);
            }
            switch ($integerVariable) {
                case "collapseLevel":
                    $configuration->$integerVariable = min(self::MAXIMUM_LEVEL, $configuration->$integerVariable);
                    break;
            }
        }
        foreach (self::getBooleanVariables() as $booleanVariable) {
            if (isset($options[$booleanVariable])
                && in_array(strval($options[$booleanVariable]), ["0", "1"])) {
                $configuration->$booleanVariable = boolval(intval($options[$booleanVariable]));
            }
        }
        return $configuration;
    }

    /**
     * @return Configuration
     */
    public static function createFromSuperglobalCookie()
    {
        $options = [];
        if (isset($_COOKIE, $_COOKIE[HtmlFormatter::COOKIE_NAME])) {
            $options = @json_decode($_COOKIE[HtmlFormatter::COOKIE_NAME], true) ?: null;
        }
        if ($options && is_array($options)) {
            $configuration = self::createFromOptionsArray($options);
        } else {
            $configuration = new self(self::DEFAULT_COLLAPSE_LEVEL);
        }
        return $configuration;
    }

    /**
     * @return array
     */
    public static function getBooleanVariables()
    {
        return [
            "areOptionsShown",
            "isShowingConstants",
            "isShowingInterfaces",
            "isShowingMethodParameters",
            "isShowingMethodParameterTypeHints",
            "isShowingMethods",
            "isShowingParentClass",
            "isShowingTraits",
            "isShowingVariables",
            "isTruncatingGenericObjects",
        ];
    }

    /**
     * @return array
     */
    public static function getIntegerVariables()
    {
        return [
            "collapseLevel",
        ];
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
