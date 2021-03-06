<?php
namespace Kafoso\Tools\Debug\Dumper;

class JsonFormatter extends AbstractFormatter
{
    public function render($var, $depth = null)
    {
        if (is_int($depth)) {
            $this->configuration->setDepth($depth);
        }
        return json_encode($this->prepareRecursively($var, 0, []), $this->configuration->getJsonOptions());
    }

    private function prepareRecursively($var, $level, array $previousSplObjectHashes)
    {
        if (is_object($var)) {
            $hash = spl_object_hash($var);
            if (in_array($hash, $previousSplObjectHashes)) {
                return $this->renderObjectRecursion($var);
            }
            $previousSplObjectHashes[] = $hash;
            if ($level >= $this->getConfiguration()->getDepth()) {
                return $this->renderObjectOmitted($var);
            } else {
                return $this->renderObject($var, ($level+1), $previousSplObjectHashes);
            }
        } elseif (is_array($var)) {
            if ($level >= $this->getConfiguration()->getDepth()) {
                return $this->renderArrayOmitted($var);
            }
            return $this->renderArray($var, ($level+1), $previousSplObjectHashes);
        } elseif (is_resource($var)) {
            return $this->renderResource($var);
        }
        return $var;
    }

    private function renderArray(array $array, $level, array $previousSplObjectHashes)
    {
        foreach ($array as $k => $v) {
            $array[$k] = $this->prepareRecursively($v, $level, $previousSplObjectHashes);
        }
        return $array;
    }

    private function renderObject($object, $level, array $previousSplObjectHashes)
    {
        $hash = spl_object_hash($object);
        $reflection = new \ReflectionObject($object);
        $properties = $reflection->getProperties();
        $array = [
            __NAMESPACE__ . "|CLASS" => get_class($object) . " Object #{$hash}",
        ];
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $array[$property->getName()] = $this->prepareRecursively(
                $property->getValue($object),
                $level,
                $previousSplObjectHashes
            );
        }
        return $array;
    }

    private function renderObjectOmitted($object)
    {
        $hash = spl_object_hash($object);
        return [
            __NAMESPACE__ . "|CLASS" => get_class($object) . " Object #{$hash}",
            __NAMESPACE__ . "|OBJECT_VALUE_OMITTED" => "(Object value omitted)",
        ];
    }

    private function renderArrayOmitted(array $array, $level = 0)
    {
        $arraySize = count($array);
        return [
            __NAMESPACE__ . "|ARRAY_VALUE_OMITTED" => "(Array value omitted; array({$arraySize}))",
        ];
    }

    private function renderObjectRecursion($object, $level = 0)
    {
        $hash = spl_object_hash($object);
        return [
            __NAMESPACE__ . "|CLASS" => get_class($object) . " Object #{$hash}",
            __NAMESPACE__ . "|RECURSION" => "*RECURSION*",
        ];
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
