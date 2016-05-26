<?php
namespace Kafoso\Tools\Debug\Dumper;

class PlainTextFormatter
{
    const INDENTATION_CHARACTERS = "  ";

    public static function prepareRecursively($var, $depth, $isTruncatingRecursion, $level,
        array $previousSplObjectHashes)
    {
        if (is_array($var) || is_object($var)) {
            if (is_object($var)) {
                $hash = spl_object_hash($var);
                if ($isTruncatingRecursion) {
                    if (in_array($hash, $previousSplObjectHashes)) {
                        return self::produceHumanReadableOutputForRecursedObject($var, $level);
                    }
                    $previousSplObjectHashes[] = $hash;
                }
            }
            if ($depth <= 0) {
                if (is_object($var)) {
                    return self::produceHumanReadableOutputForOmittedObject($var, $level);
                } else {
                    return self::produceHumanReadableOutputForOmittedArray($var, $level);
                }
            } else {
                if (is_object($var)) {
                    return self::produceHumanReadableOutputForObject($var, $depth, $isTruncatingRecursion, $level,
                        $previousSplObjectHashes);
                } else {
                    return self::produceHumanReadableOutputForArray($var, $depth, $isTruncatingRecursion, $level,
                        $previousSplObjectHashes);
                }
            }
        }
        if (is_scalar($var)) {
            $var = self::produceHumanReadableOutputForScalarType($var);
        } elseif (is_null($var)) {
            $var = "NULL";
        }
        return str_repeat(self::INDENTATION_CHARACTERS, $level) . $var . PHP_EOL;
    }

    public static function produceHumanReadableOutputForArray(array $array, $depth, $isTruncatingRecursion, $level,
        array $previousSplObjectHashes)
    {
        $arrayAsString = "";
        foreach ($array as $k => $v) {
            $replacementValue = self::prepareRecursively(
                $v,
                ($depth - 1),
                $isTruncatingRecursion,
                ($level + 1),
                $previousSplObjectHashes
            );
            $sprintfPattern = "[\"%s\"] => %s,";
            if (is_int($k)) {
                $sprintfPattern = "[%s] => %s,";
            }
            $arrayAsString .= str_repeat(self::INDENTATION_CHARACTERS, ($level+1)) . sprintf(
                $sprintfPattern,
                $k,
                trim($replacementValue)
            ) . PHP_EOL;
        }
        $arraySize = count($array);
        return str_repeat(self::INDENTATION_CHARACTERS, $level) . "array({$arraySize}) {" . PHP_EOL
            . $arrayAsString
            . str_repeat(self::INDENTATION_CHARACTERS, $level) . "}"  . PHP_EOL;
        return implode(PHP_EOL, $array);
    }

    public static function produceHumanReadableOutputForObject($object, $depth, $isTruncatingRecursion, $level,
        array $previousSplObjectHashes)
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
            $replacementValue = self::prepareRecursively(
                $propertyValue,
                ($depth - 1),
                $isTruncatingRecursion,
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
            $objectValuesString .= str_repeat(self::INDENTATION_CHARACTERS, ($level+1)) . str_replace(
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
        return str_repeat(self::INDENTATION_CHARACTERS, $level) . get_class($object) . " Object &{$hash}" . PHP_EOL
            . str_repeat(self::INDENTATION_CHARACTERS, $level) . "{" . PHP_EOL
            . $objectValuesString
            . str_repeat(self::INDENTATION_CHARACTERS, $level) . "}" . PHP_EOL;
    }

    public static function produceHumanReadableOutputForOmittedObject($object, $level = 0)
    {
        $hash = spl_object_hash($object);
        return str_repeat(self::INDENTATION_CHARACTERS, $level) . get_class($object) . " Object &{$hash}" . PHP_EOL
            . str_repeat(self::INDENTATION_CHARACTERS, $level) . "{" . PHP_EOL
            . str_repeat(self::INDENTATION_CHARACTERS, ($level+1)) . "(Object value omitted)" . PHP_EOL
            . str_repeat(self::INDENTATION_CHARACTERS, $level) . "}"  . PHP_EOL;
    }

    public static function produceHumanReadableOutputForOmittedArray(array $array, $level = 0)
    {
        $arraySize = count($array);
        return str_repeat(self::INDENTATION_CHARACTERS, $level) . "array({$arraySize}) {" . PHP_EOL
            . str_repeat(self::INDENTATION_CHARACTERS, ($level+1)) . "(Array value omitted)" . PHP_EOL
            . str_repeat(self::INDENTATION_CHARACTERS, $level) . "}"  . PHP_EOL;
    }

    public static function produceHumanReadableOutputForRecursedObject($object, $level = 0)
    {
        $hash = spl_object_hash($object);
        return str_repeat(self::INDENTATION_CHARACTERS, $level) . get_class($object) . " Object &{$hash}" . PHP_EOL
            . str_repeat(self::INDENTATION_CHARACTERS, $level) . "{" . PHP_EOL
            . str_repeat(self::INDENTATION_CHARACTERS, ($level+1)) . "*RECURSION*" . PHP_EOL
            . str_repeat(self::INDENTATION_CHARACTERS, $level) . "}"  . PHP_EOL;
    }

    public static function produceHumanReadableOutputForScalarType($scalarValue)
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
}
