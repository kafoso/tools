<?php
namespace Kafoso\Tools\Debug;

class VariableDumper
{
    const CAST_ARRAY_CONTENT_MAX_LENGTH = 64;

    public static function cast($value)
    {
        if (is_scalar($value)) {
            if (is_bool($value)) {
                return ($value ? "true" : "false");
            }
            return strval($value);
        }
        if (is_null($value)) {
            return "null";
        }
        if (is_array($value)) {
            return self::castArray($value);
        }
        if (is_object($value)) {
            if (method_exists($value, "__toString")) {
                return (string)$value;
            }
            return sprintf(
                "(object \\%s)",
                get_class($value)
            );
        }
        if (is_resource($value)) {
            return sprintf(
                "(resource #%s (type: %s))",
                intval($value),
                get_resource_type($value)
            );
        }
        return @strval($value);
    }

    public static function castArray(array $array)
    {
        $arraySize = count($array);
        $str = "Array({$arraySize}) [";
        $eol = chr(10);
        $weol = chr(13) . $eol;
        $ellipsis = " ... ";
        $index = 0;
        foreach ($array as $k => $v) {
            if (preg_match("/^\d+$/", $k)) {
                $append = "[{$k}]";
            } else {
                $append = "[\"{$k}\"]";
            }
            if (is_scalar($v) || is_null($v)) {
                if (is_null($v)) {
                    $append .= " => NULL";
                } elseif (is_bool($v)) {
                    $append .= " => " . ($v ? "true" : "false");
                } elseif (is_string($v)) {
                    $v = str_replace(array($eol, $weol, "\\t"), " ", $v);
                    $append .= " => \"" . strval($v) . "\"";
                } else {
                    $append .= " => " . strval($v);
                }
            } else if (is_array($v)) {
                $append .= " => array(" . count($v) . ")";
            } else if (is_object($v)) {
                $append .= " => Object";
            }
            if (mb_strlen($str . $append) > self::CAST_ARRAY_CONTENT_MAX_LENGTH) {
                $substr = substr($append, 0, (self::CAST_ARRAY_CONTENT_MAX_LENGTH - mb_strlen($str)));
                $str .= $substr . $ellipsis;
                break;
            }
            if ($index > 0) {
                $str .= ", ";
            }
            $str .= $append;
            $index++;
        }
        $str .= "]";
        return $str;
    }

    public static function found($value)
    {
        if (is_float($value) || is_int($value)) {
            return sprintf(
                "(%s) %s",
                gettype($value),
                strval($value)
            );
        }
        if (is_bool($value)) {
            return sprintf(
                "(%s) %s",
                gettype($value),
                ($value ? "true" : "false")
            );
        }
        if (is_string($value)) {
            return sprintf(
                "(%s) %s",
                gettype($value),
                $value
            );
        }
        if (is_null($value)) {
            return "(null) null";
        }
        if (is_array($value)) {
            return sprintf(
                "(array) Array(%s)",
                count($value)
            );
        }
        if (is_object($value)) {
            return sprintf(
                "(object) \\%s",
                get_class($value)
            );
        }
        if (is_resource($value)) {
            return sprintf(
                "(resource) #%s (type: %s)",
                intval($value),
                get_resource_type($value)
            );
        }
        return "(unkown)";
    }
}
