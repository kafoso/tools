<?php
namespace Kafoso\Tools\Debug\Dumper;

class JsonFormatter
{
    public static function prepareRecursively($var, $depth, array $previousSplObjectHashes)
    {
        if (is_array($var) || is_object($var)) {
            if (is_object($var)) {
                $hash = spl_object_hash($var);
                if (in_array($hash, $previousSplObjectHashes)) {
                    return self::produceHumanReadableOutputForRecursedObject($var);
                }
                $previousSplObjectHashes[] = $hash;
            }
            if ($depth <= 0) {
                if (is_object($var)) {
                    return self::produceHumanReadableOutputForOmittedObject($var);
                } else {
                    return self::produceHumanReadableOutputForOmittedArray($var);
                }
            } else {
                if (is_object($var)) {
                    return self::produceHumanReadableOutputForObject($var, $depth, $previousSplObjectHashes);
                } else {
                    return self::produceHumanReadableOutputForArray($var, $depth, $previousSplObjectHashes);
                }
            }
        }
        return $var;
    }

    public static function produceHumanReadableOutputForArray(array $array, $depth, array $previousSplObjectHashes)
    {
        foreach ($array as $k => $v) {
            $array[$k] = self::prepareRecursively($v, ($depth-1), $previousSplObjectHashes);
        }
        return $array;
    }

    public static function produceHumanReadableOutputForObject($object, $depth, array $previousSplObjectHashes)
    {
        $hash = spl_object_hash($object);
        $reflection = new \ReflectionObject($object);
        $properties = $reflection->getProperties();
        $array = [
            __NAMESPACE__ . "|CLASS" => get_class($object) . " Object &{$hash}",
        ];
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $array[$property->getName()] = self::prepareRecursively(
                $property->getValue($object),
                ($depth-1),
                $previousSplObjectHashes
            );
        }
        return $array;
    }

    public static function produceHumanReadableOutputForOmittedObject($object)
    {
        $hash = spl_object_hash($object);
        return [
            __NAMESPACE__ . "|CLASS" => get_class($object) . " Object &{$hash}",
            __NAMESPACE__ . "|OBJECT_VALUE_OMITTED" => "(Object value omitted)",
        ];
    }

    public static function produceHumanReadableOutputForOmittedArray(array $array, $level = 0)
    {
        $arraySize = count($array);
        return [
            __NAMESPACE__ . "|ARRAY_VALUE_OMITTED" => "(Array value omitted; array({$arraySize}))",
        ];
    }

    public static function produceHumanReadableOutputForRecursedObject($object, $level = 0)
    {
        $hash = spl_object_hash($object);
        return [
            __NAMESPACE__ . "|CLASS" => get_class($object) . " Object &{$hash}",
            __NAMESPACE__ . "|RECURSION" => "*RECURSION*",
        ];
    }
}
