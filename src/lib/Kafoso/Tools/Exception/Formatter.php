<?php
namespace Kafoso\Tools\Exception;

abstract class Formatter
{
    const PLAIN_TEXT_INDENTATION = "    ";

    /**
     * @param \Exception|Throwable $e
     * @return number
     */
    public static function countPreviousExceptions($e)
    {
        $count = 0;
        $previous = $e->getPrevious();
        while ($previous) {
            $count++;
            $previous = $previous->getPrevious();
        }
        return $count;
    }

    /**
     * @param \Exception|\Throwable $e
     * @param number $previousExceptionDepth
     * @return string
     */
    public static function formatForErrorLog($e, $previousExceptionDepth = 2)
    {
        $str = sprintf(
            "%s: \"%s\" in %s:%s with code %s. Stacktrace: [%s]",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getCode(),
            $e->getTraceAsString()
        );
        if ($e->getPrevious()) {
            $str .= " Previous exception: ";
            if (0 < ($previousExceptionDepth - 1)) {
                $str .= self::formatForErrorLog($e->getPrevious(), ($previousExceptionDepth - 1));
            } else {
                $previousException = $e->getPrevious();
                $previousExceptionCount = 0;
                while ($previousException instanceof \Exception) {
                    $previousExceptionCount++;
                    $previousException = $previousException->getPrevious();
                }
                $str .= sprintf(
                    "(%d more exception%s omitted)",
                    $previousExceptionCount,
                    (1 == $previousExceptionCount ? "" : "s")
                );
            }
        }
        return $str;
    }

    /**
     * @param \Exception|\Throwable $e
     * @param number $previousExceptionDepth
     * @return string
     */
    public static function formatPlainText($e, $previousExceptionDepth = 2)
    {
        $str = get_class($e) . " [" . date("c") . "]" . PHP_EOL;
        $str .= self::formatPlainTextInner($e, $previousExceptionDepth, 0);
        return $str;
    }

    /**
     * Displays a value as JSON. If an array, only the first level of keysare shown.
     * @param mixed $value                      Any data type.
     * @param integer $options                  Same interface as in json_encode.
     * @return string
     */
    public static function asFlattenedJson($value, $options = 0)
    {
        if (is_array($value)) {
            foreach ($value as &$v) {
                $v = self::cast($v);
            }
        } elseif (is_object($value) || is_resource($value)) {
            $value = self::cast($value);
        }
        return json_encode($value, $options, 1);
    }

    /**
     * Ensures a string value is always returned so that e.g. null won't hide.
     * @param mixed $value                      Any data type.
     * @return string
     */
    public static function cast($value)
    {
        if (is_object($value)) {
            return sprintf(
                "\\%s",
                get_class($value)
            );
        }
        if (is_array($value)) {
            return "Array";
        }
        if (is_bool($value)) {
            return ($value ? "true" : "false");
        }
        if (is_null($value)) {
            return "null";
        }
        if (is_resource($value)) {
            return "#{$value}";
        }
        return @strval($value);
    }

    /**
     * Shows the data type and the flattened value as a string.
     * @param mixed $value                      Any data type.
     * @return string
     */
    public static function found($value)
    {
        if (is_object($value)) {
            return sprintf(
                "(object) %s",
                get_class($value)
            );
        }
        if (is_array($value)) {
            return "(array)";
        }
        if (is_bool($value)) {
            return sprintf(
                "(boolean) %s",
                ($value ? "true" : "false")
            );
        }
        if (is_null($value)) {
            return "(null) null";
        }
        if (is_resource($value)) {
            return "(resource) {$value}";
        }
        return sprintf(
            "(%s) %s",
            gettype($value),
            @strval($value)
        );
    }

    /**
     * @param object $object
     * @return string
     */
    public static function getOriginalClassName($object)
    {
        if (is_object($object)) {
            if (class_exists('PHPUnit_Framework_MockObject_MockObject')
                && $object instanceof \PHPUnit_Framework_MockObject_MockObject) {
                return get_parent_class($object);
            } elseif (class_exists('Doctrine\Common\Proxy\Proxy')
                && $object instanceof \Doctrine\Common\Proxy\Proxy) {
                $className = \Doctrine\Common\Util\ClassUtils::getRealClass(get_class($object));
                if ($className) {
                    return $className;
                }
            }
            return get_class($object);
        }
        return "";
    }

    /**
     * @param \Exception|\Throwable $e
     * @param unknown $previousExceptionDepth
     * @param unknown $level
     * @return string
     */
    protected static function formatPlainTextInner($e, $previousExceptionDepth, $level)
    {
        $indentation = self::genereateIndentationFromLevel($level);
        $message = $e->getMessage();
        $str = "{$indentation}Message:" . PHP_EOL . self::formatPlainTextMessage($message, ($level+1));
        $str .= "{$indentation}File: " . $e->getFile() . PHP_EOL;
        $str .= "{$indentation}Line: " . $e->getLine() . PHP_EOL;
        $str .= "{$indentation}Code: " . $e->getCode() . PHP_EOL;
        $str .= "{$indentation}Exception class: \\" . get_class($e) . PHP_EOL;
        $str .= "{$indentation}Stacktrace:" . PHP_EOL . self::formatPlainTextStacktrace($e, ($level+1));
        $str .= "{$indentation}Previous exception:";
        if ($e->getPrevious()) {
            if (0 < $previousExceptionDepth) {
                $str .= PHP_EOL;
                $str .= self::formatPlainTextInner(
                    $e->getPrevious(),
                    ($previousExceptionDepth-1),
                    ($level+1)
                );
            } else {
                $previousExceptionCount = self::countPreviousExceptions($e);
                $str .= sprintf(
                    " (%d previous %s)",
                    $previousExceptionCount,
                    (1 == abs($previousExceptionCount) ? "exception" : "exceptions")
                );
            }
        } else {
            $str .= " (None)";
        }
        return $str;
    }

    /**
     * @param string $message
     * @param int $level
     * @return string
     */
    protected static function formatPlainTextMessage($message, $level)
    {
        $indentation = self::genereateIndentationFromLevel($level);
        $messageArray = preg_split("/[\r\n]/", $message);
        $str = "";
        foreach ($messageArray as $line) {
            $str .= $indentation . $line . PHP_EOL;
        }
        return $str;
    }

    /**
     * @param \Exception|\Throwable $e
     * @param int $level
     * @return string
     */
    protected static function formatPlainTextStacktrace($e, $level)
    {
        $indentation = self::genereateIndentationFromLevel($level);
        $stacktrace = $e->getTraceAsString();
        $stacktraceArray = preg_split("/[\r\n]/", $stacktrace);
        $str = "";
        foreach ($stacktraceArray as $line) {
            $str .= $indentation . $line . PHP_EOL;
        }
        return $str;
    }

    /**
     * @param int $level
     * @return string
     */
    protected static function genereateIndentationFromLevel($level)
    {
        return str_repeat(self::PLAIN_TEXT_INDENTATION, $level);
    }
}
