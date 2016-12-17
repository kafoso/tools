<?php
namespace Kafoso\Tools\Debug\Dumper;

class PlainTextFormatter extends AbstractFormatter
{
    const INDENTATION_CHARACTER_COUNT = 2;

    private $isTruncatingRecursion;

    public function __construct($var, $depth = null, $isTruncatingRecursion = true)
    {
        parent::__construct($var, $depth);
        $this->isTruncatingRecursion = $isTruncatingRecursion;
    }

    public function render()
    {
        return $this->prepareRecursively($this->var, $this->depth, 0, []);
    }

    private function prepareRecursively(
        $var,
        $depth,
        $level,
        array $previousSplObjectHashes
    )
    {
        if (is_array($var) || is_object($var)) {
            if (is_object($var)) {
                $hash = spl_object_hash($var);
                if ($this->isTruncatingRecursion) {
                    if (in_array($hash, $previousSplObjectHashes)) {
                        return $this->renderObjectRecursion($var, $level);
                    }
                    $previousSplObjectHashes[] = $hash;
                }
            }
            if ($depth <= 0) {
                if (is_object($var)) {
                    return $this->renderObjectOmitted($var, $level);
                } else {
                    return $this->renderArrayOmitted($var, $level);
                }
            } else {
                if (is_object($var)) {
                    return $this->renderObject(
                        $var,
                        $depth,
                        $level,
                        $previousSplObjectHashes
                    );
                } else {
                    return $this->renderArray(
                        $var,
                        $depth,
                        $level,
                        $previousSplObjectHashes
                    );
                }
            }
        } elseif (is_resource($var)) {
            return $this->renderResource($var);
        } elseif (is_scalar($var)) {
            $var = $this->renderDefault($var);
        } elseif (is_null($var)) {
            $var = "null";
        }
        return str_repeat($this->getIndentationCharacters(), $level) . $var;
    }

    private function renderArray(
        array $array,
        $depth,
        $level,
        array $previousSplObjectHashes
    )
    {
        $arrayAsString = "";
        foreach ($array as $k => $v) {
            $replacementValue = $this->prepareRecursively(
                $v,
                ($depth - 1),
                ($level + 1),
                $previousSplObjectHashes
            );
            $sprintfPattern = "[\"%s\"] => %s,";
            if (is_int($k)) {
                $sprintfPattern = "[%s] => %s,";
            }
            $arrayAsString .= str_repeat($this->getIndentationCharacters(), ($level+1)) . sprintf(
                $sprintfPattern,
                $k,
                trim($replacementValue)
            ) . PHP_EOL;
        }
        $arraySize = count($array);
        $indentation = str_repeat($this->getIndentationCharacters(), $level);
        return $indentation . "array({$arraySize}) {" . PHP_EOL
            . $arrayAsString
            . $indentation . "}";
    }

    private function renderObject(
        $object,
        $depth,
        $level,
        array $previousSplObjectHashes
    )
    {
        if (false == is_object($object)) {
            throw new \UnexpectedValueException(sprintf(
                "Expected parameter '%s' to be an Object. Found: %s",
                '$object',
                gettype($object)
            ));
        }
        $reflection = new \ReflectionObject($object);
        $properties = $reflection->getProperties();
        $objectValuesString = "";
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $propertyValue = $property->getValue($object);
            $replacementValue = $this->prepareRecursively(
                $propertyValue,
                ($depth - 1),
                ($level + 1),
                $previousSplObjectHashes
            );
            $exposure = "";
            if ($property->isPrivate()) {
                $exposure = "private";
            } elseif ($property->isProtected()) {
                $exposure = "protected";
            } else {
                $exposure = "public";
            }
            if ($property->isStatic()) {
                $exposure .= " static";
            }
            $objectValuesString .= str_repeat($this->getIndentationCharacters(), ($level+1)) . str_replace(
                [
                    "%PROPERTY_EXPOSURE%",
                    "%PROPERTY_NAME%",
                    "%PROPERTY_VALUE%",
                ],
                [
                    $exposure,
                    $property->getName(),
                    trim($replacementValue),
                ],
                "%PROPERTY_EXPOSURE% $%PROPERTY_NAME% = %PROPERTY_VALUE%"
            );
            if (is_scalar($propertyValue) || is_null($propertyValue)) {
                $objectValuesString .= ";";
            }
            $objectValuesString .= PHP_EOL;
        }
        $hash = spl_object_hash($object);
        $indentation = str_repeat($this->getIndentationCharacters(), $level);
        return $indentation . get_class($object) . " Object &{$hash}" . PHP_EOL
            . $indentation
            . "{"
            . PHP_EOL
            . $objectValuesString
            . $indentation
            . "}";
    }

    private function renderObjectOmitted($object, $level = 0)
    {
        $hash = spl_object_hash($object);
        $indentation = str_repeat($this->getIndentationCharacters(), $level);
        $indentationInner = str_repeat($this->getIndentationCharacters(), ($level+1));
        return $indentation
            . get_class($object)
            . " Object &{$hash}"
            . PHP_EOL
            . $indentation
            . "{"
            . PHP_EOL
            . $indentationInner
            . "(Object value omitted)"
            . PHP_EOL
            . $indentation
            . "}"
            . PHP_EOL;
    }

    private function renderArrayOmitted(array $array, $level = 0)
    {
        $arraySize = count($array);
        $indentation = str_repeat($this->getIndentationCharacters(), $level);
        $indentationInner = str_repeat($this->getIndentationCharacters(), ($level+1));
        return $indentation
            . "array({$arraySize}) {"
            . PHP_EOL
            . $indentationInner
            . "(Array value omitted)"
            . PHP_EOL
            . $indentation
            . "}"
            . PHP_EOL;
    }

    private function renderObjectRecursion($object, $level = 0)
    {
        $hash = spl_object_hash($object);
        $indentation = str_repeat($this->getIndentationCharacters(), $level);
        $indentationInner = str_repeat($this->getIndentationCharacters(), ($level+1));
        return $indentation
            . get_class($object)
            . " Object &{$hash}"
            . PHP_EOL
            . $indentation
            . "{"
            . PHP_EOL
            . $indentationInner
            . "*RECURSION*"
            . PHP_EOL
            . $indentation
            . "}"
            . PHP_EOL;
    }

    private function renderDefault($scalarValue)
    {
        if (false == is_scalar($scalarValue)) {
            throw new \InvalidArgumentException(sprintf(
                "Expected parameter '%s' to be a scalar value. Found: %s",
                '$scalarValue',
                gettype($scalarValue)
            ));
        }
        switch (gettype($scalarValue)) {
            case "boolean":
                return sprintf(
                    "bool(%s)",
                    ($scalarValue ? "true" : "false")
                );
                break;
            case "double":
                return "float($scalarValue)";
            case "integer":
                return "int($scalarValue)";
        }
        return sprintf(
            "string(%s) \"%s\"",
            mb_strlen($scalarValue, "UTF-8"),
            preg_replace('/\"/', '\\"', $scalarValue)
        );
    }

    private function renderResource($resource)
    {
        return sprintf(
            'Resource #%d (Type: %s)',
            intval($resource),
            get_resource_type($resource)
        );
    }
}
