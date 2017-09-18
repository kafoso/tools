<?php
namespace Kafoso\Tools\Debug\Dumper\JsonFormatter;

use Kafoso\Tools\Debug\Dumper\AbstractConfiguration;

class Configuration extends AbstractConfiguration
{
    private $jsonOptions = JSON_PRETTY_PRINT;

    /**
     * @param int $jsonOptions
     * @return $this
     */
    public function setJsonOptions($jsonOptions)
    {
        $this->jsonOptions = $jsonOptions;
        return $this;
    }

    /**
     * @return int
     */
    public function getJsonOptions()
    {
        return $this->jsonOptions;
    }
}
