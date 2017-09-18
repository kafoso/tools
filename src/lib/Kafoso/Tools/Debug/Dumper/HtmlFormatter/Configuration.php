<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter;

use Kafoso\Tools\Debug\Dumper\AbstractConfiguration;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter;
use Kafoso\Tools\Exception\Formatter;

class Configuration extends AbstractConfiguration
{
    private $areOptionsShown = true;
    private $isShowingConstants = false;
    private $isShowingInterfaces = true;
    private $isShowingMethodParameters = true;
    private $isShowingMethodParameterTypeHints = true;
    private $isShowingMethods = true;
    private $isShowingParentClass = true;
    private $isShowingTraits = true;
    private $isShowingVariables = true;

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
     * @return Configuration
     */
    public static function createFromOptionsArray(array $options)
    {
        $configuration = new self;
        foreach (self::getIntegerOptions() as $integerVariable) {
            if (isset($options[$integerVariable])) {
                $configuration->$integerVariable = intval($options[$integerVariable]);
            }
            switch ($integerVariable) {
                case "collapseLevel":
                    $configuration->$integerVariable = min(self::MAXIMUM_LEVEL, $configuration->$integerVariable);
                    break;
            }
        }
        foreach (self::getBooleanOptions() as $booleanVariable) {
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
            $configuration = new self;
        }
        return $configuration;
    }

    /**
     * @return array
     */
    public static function getBooleanOptions()
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
    public static function getIntegerOptions()
    {
        return [
            "collapseLevel",
        ];
    }
}
