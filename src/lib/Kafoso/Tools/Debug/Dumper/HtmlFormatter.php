<?php
namespace Kafoso\Tools\Debug\Dumper;

use Kafoso\Tools\Debug\Dumper;
use Kafoso\Tools\HTML\ViewRenderer;

class HtmlFormatter extends AbstractFormatter
{
    const PSR_2_SOFT_CHARACTER_LIMIT = 120;

    public function generateIndentationForLevel($level)
    {
        $indentationCharacters = $this->getIndentationCharacters();
        return str_repeat(sprintf(
            '<span class="invisible-character leading-whitespace indent-guide">%s</span>',
            $indentationCharacters
        ), $level);
    }

    public function render()
    {
        $origin = $this->getOrigin();
        $viewRenderer = new ViewRenderer("Kafoso/Tools/Debug/Dumper/HtmlFormatter/render.phtml", [
            'PSR_2_SOFT_CHARACTER_LIMIT' => self::PSR_2_SOFT_CHARACTER_LIMIT,
            'css' => $this->getcss(),
            'innerHtml' => $this->renderInner(),
            'javascript' => $this->getJavascript(),
            'origin' => $origin,
        ]);
        $viewRenderer->setBaseDirectory(realpath(__DIR__ . str_repeat("/..", 5)) . "/view");
        return $viewRenderer->render();
    }

    public function renderInner()
    {
        return $this->prepareRecursively($this->var, $this->depth, 0, []);
    }

    public function getCss()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat("/..", 6));
        $css = file_get_contents($baseDirectory . "/resources/Kafoso/Tools/Debug/Dumper/HtmlFormatter/theme/dark-one-ui.css");
        return $css;
    }

    public function getJavascript()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat("/..", 6));
        $js = file_get_contents($baseDirectory . "/resources/Kafoso/Tools/Debug/Dumper/HtmlFormatter/js/main.js");
        return $js;
    }

    /**
     * Looks back through the debug_backtrace to determine from where the output originated.
     * @return ?array
     */
    public function getOrigin()
    {
        $calledFrom = null;
        $rootDirectory = realpath(__DIR__ . str_repeat("/..", 5));
        foreach (debug_backtrace() as $v) {
            if (0 === stripos($v['file'], $rootDirectory)) {
                continue;
            }
            $calledFrom = $v;
            break;
        }
        return $calledFrom;
    }

    private function prepareRecursively(
        $var,
        $depth,
        $level,
        array $previousSplObjectHashes,
        $doPrependWhitespace = true
    )
    {
        $indentation = "";
        if ($doPrependWhitespace) {
            $indentation = $this->generateIndentationForLevel($level);
        }
        if (is_object($var)) {
            if ($depth <= 0) {
                return $indentation . $this->renderObjectOmitted($var);
            }
            $hash = spl_object_hash($var);
            if (in_array($hash, $previousSplObjectHashes)) {
                return $indentation . $this->renderObjectRecursion($var);
            }
            $previousSplObjectHashes[] = $hash;
            return $indentation . $this->renderObject($var, $depth, $level, $previousSplObjectHashes);
        } elseif (is_array($var)) {
            if ($depth <= 0) {
                return $indentation . $this->renderArrayOmitted($var);
            }
            return $this->renderArray($var, $depth, $level, $previousSplObjectHashes, $doPrependWhitespace);
        } elseif (is_resource($var)) {
            return $indentation . $this->renderResource($var);
        }
        return $indentation . $this->renderScalarOrNull($var);
    }

    public function renderArray(
        array $array,
        $depth,
        $level,
        array $previousSplObjectHashes,
        $doPrependWhitespace = true
    )
    {
        if ($array) {
            $indentation = $this->generateIndentationForLevel($level);
            $indentationInner = $this->generateIndentationForLevel($level+1);
            $html = $indentation . '[' . PHP_EOL;
            if (false == $doPrependWhitespace) {
                $html = '[' . PHP_EOL;
            }
            foreach ($array as $k => $v) {
                $html .= $indentationInner;
                $html .= $this->renderScalarOrNull($k);
                $html .= ' => ';
                $html .= $this->prepareRecursively($v, ($depth-1), ($level+1), $previousSplObjectHashes, false);
                $html .= ",";
                $html .= PHP_EOL;
            }
            $html .= $indentation . ']';
        } else {
            $html = '[]';
        }
        if ($level > 0 && '[]' != $html) {
            $html = '<span class="collapsible expanded">' . $html . '</span>';
        }
        return $html;
    }

    public function renderArrayOmitted(array $array, $level = 0)
    {
        return sprintf(
            "(Array(%d); omitted)",
            count($array)
        );
    }

    private function renderScalarOrNull($value)
    {
        $commentHtml = null;
        $class = "";["syntax--language syntax--php"];
        if (is_bool($value) || is_null($value) || is_float($value) || is_int($value)) {
            $class = "syntax--language syntax--php syntax--constant";
            if (is_null($value)) {
                $value = "null";
            } elseif (is_bool($value)) {
                $value = ($value ? "true" : "false");
            } elseif (is_float($value) || is_int($value)) {
                $class .= " syntax--numeric";
            }
        } else {
            $class = "syntax--language syntax--php syntax--string";
            $value = strval($value);
            $regexAsciiCharacters = '[\x00-\x1F\x7F]';
            $split = preg_split("/(\\$|\\\"|\\\\|$regexAsciiCharacters)/", $value, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            if (count($split) > 1) {
                $value = "";
                $i = 0;
                foreach ($split as $segment) {
                    $subClass = "syntax--constant syntax--character syntax--escape syntax--php";
                    if (0 == $i%2) {
                        $value .= htmlentities($segment);
                    } else {
                        if ("\e" === $segment) {
                            $segment = "\\e";
                        } elseif ("\n" === $segment) {
                            $segment = "\\n";
                        } elseif ("\r" === $segment) {
                            $segment = "\\r";
                        } elseif ("\t" === $segment) {
                            $segment = "\\t";
                        } else {
                            preg_match("/$regexAsciiCharacters/", $segment, $match);
                            if ($match) {
                                $segment = "\\x" . bin2hex($match[0]);
                                $subClass = "syntax--constant syntax--numeric syntax--octal syntax--php";
                            } else {
                                $segment = "\\" . $segment;
                            }
                        }
                        $value .= sprintf(
                            '<span class="%s">%s</span>',
                            $subClass,
                            htmlentities($segment)
                        );
                    }
                    $i++;
                }
                $quot = htmlentities('"');
                $value = "{$quot}{$value}{$quot}";
            } else {
                $value = htmlentities('"' . $value . '"');
            }
        }
        return sprintf(
            '<span class="%s">%s</span>',
            htmlentities($class),
            $value
        );
    }

    private function renderObject($object, $depth, $level, array $previousSplObjectHashes)
    {
        $self = $this;
        $hash = spl_object_hash($object);
        $reflectionObject = new \ReflectionObject($object);
        $indentation = $this->generateIndentationForLevel($level);
        $indentationInner = $this->generateIndentationForLevel($level+1);
        $className = get_class($object);

        $entity = [];
        if ($reflectionObject->isFinal()) {
            $entity[] = "final";
        }
        if ($reflectionObject->isAbstract()) {
            $entity[] = "abstract";
        }
        if ($reflectionObject->isTrait()) {
            $entity[] = "trait";
        } else {
            $entity[] = "class";
        }
        $entityBlockHtml = '';
        $entity = implode(" ", $entity);
        $entityBlockHtml .= sprintf(
            '<span class="syntax--storage syntax--type syntax--class syntax--php">%s</span> <a title="%s" name="%s"><span class="syntax--entity syntax--name syntax--class">\\%s</span></a>',
            $entity,
            htmlentities("Object #{$hash}"),
            "Object_{$hash}",
            $className
        );
        $entityBlockText = "{$indentation}{$entity} {$className}";
        if ($reflectionObject->getParentClass()) {
            $extendsText = " extends " . $reflectionObject->getParentClass()->getName();
            $substrLength = mb_strlen($entityBlockText)+mb_strlen($extendsText);
            if ($substrLength > self::PSR_2_SOFT_CHARACTER_LIMIT) {
                $entityBlockText .= PHP_EOL . $indentationInner;
                $entityBlockHtml .= PHP_EOL . $indentationInner;
            } else {
                $entityBlockText .= " ";
                $entityBlockHtml .= " ";
            }
            $entityBlockText .= "extends " . $reflectionObject->getParentClass()->getName();
            $entityBlockHtml .= sprintf(
                '<span class="syntax--storage syntax--modifier syntax--extends syntax--php">extends</span> <span class="syntax--storage syntax--modifier syntax--extends syntax--php">\\%s</span>',
                htmlentities($reflectionObject->getParentClass()->getName())
            );
        }
        if ($reflectionObject->getInterfaces()) {
            $interfaceNamesText = [];
            $interfaceNamesHtml = [];
            foreach ($reflectionObject->getInterfaces() as $interface) {
                $interfaceNamesText[] = $interface->getName();
                $interfaceNamesHtml[] = sprintf(
                    '<span class="syntax--meta syntax--other syntax--inherited-class syntax--php"><span class="syntax--entity syntax--other syntax--inherited-class syntax--php">\\%s</span></span>',
                    htmlentities($interface->getName())
                );
            }
            $entityBlockTextArray = explode(PHP_EOL, $entityBlockText);
            $entityBlockTextLastLine = end($entityBlockTextArray);
            $implementsText = " implements " . implode(", ", $interfaceNamesText);
            $substrLength = mb_strlen($entityBlockTextLastLine)+mb_strlen($implementsText);
            if ($substrLength > self::PSR_2_SOFT_CHARACTER_LIMIT) {
                $entityBlockText .= PHP_EOL . $indentationInner;
                $entityBlockHtml .= PHP_EOL . $indentationInner;
            } else {
                $entityBlockText .= " ";
                $entityBlockHtml .= " ";
            }
            $entityBlockText .= "implements " . $interface->getName();
            $entityBlockHtml .= sprintf(
                '<span class="syntax--storage syntax--modifier syntax--implements syntax--php">implements</span> %s',
                implode(", ", $interfaceNamesHtml)
            );
        }
        $entityBlockHtml .= PHP_EOL . $indentation . '<span>{</span>' . PHP_EOL;

        $innerHtml = [];

        // Traits
        if ($reflectionObject->getTraits()) {
            $innerHtml[] = $indentationInner . sprintf(
                '<span class="syntax--comment syntax--line syntax--double-slash syntax--php">%s</span>',
                "// Traits"
            );
            foreach ($reflectionObject->getTraits() as $trait) {
                $innerHtml[] = $indentationInner . sprintf(
                    '<span class="syntax--keyword syntax--other syntax--use syntax--php">use</span> <span class="syntax--support syntax--other syntax--namespace syntax--use syntax--php">\\%s</span>;',
                    htmlentities($trait->getName())
                );
            }
        }

        // Constants
        $constants = $reflectionObject->getConstants();
        if ($constants) {
            $renderConstant = function($name, $value) use ($self, $indentationInner){
                $commentHtml = null;
                if (is_string($value)) {
                    $commentHtml = sprintf(
                        ' <span class="syntax--comment syntax--line syntax--double-slash syntax--php">// Length: %d</span>',
                        mb_strlen($value)
                    );
                }
                return $indentationInner . sprintf(
                    '<span class="syntax--storage">const</span> <span class="syntax--constant">%s</span> = %s;',
                    htmlentities($name),
                    $self->renderScalarOrNull($value)
                ) . $commentHtml;
            };
            $innerHtml[] = $indentationInner . sprintf(
                '<span class="syntax--comment syntax--line syntax--double-slash syntax--php">%s</span>',
                "// Constants"
            );
            foreach ($constants as $k => $v) {
                $innerHtml[] = $renderConstant($k, $v);
            }
        }

        // Variables
        $properties = $reflectionObject->getProperties();
        if ($properties) {
            $propertiesDeclaredInClass = [];
            $propertiesDeclaredAtRuntime = [];
            $propertiesInherited = [];
            $renderProperty = function(\ReflectionProperty $property) use (&$self, $indentationInner, $object, $depth, $level, $previousSplObjectHashes){
                $property->setAccessible(true);
                $storage = [];
                if ($property->isPrivate()) {
                    $storage[] = "private";
                } elseif ($property->isProtected()) {
                    $storage[] = "protected";
                } elseif ($property->isPublic()) {
                    $storage[] = "public";
                }
                if ($property->isStatic()) {
                    $storage[] = "static";
                }
                $storage = implode(' ', $storage);
                $html = '';
                $html .= $indentationInner;
                $html .= sprintf(
                    '<span class="syntax--storage syntax--modifier syntax--php">%s</span> <span class="syntax--variable">$%s</span>',
                    $storage,
                    $property->getName()
                );
                $html .= " = ";
                $html .= $this->prepareRecursively(
                    $property->getValue($object),
                    ($depth-1),
                    ($level+1),
                    $previousSplObjectHashes,
                    false
                );
                if (false == is_resource($property->getValue($object))) {
                    $html .= ";";
                }
                $commentHtmlInner = null;
                if (is_string($property->getValue($object))) {
                    $commentHtmlInner = "Length: " . mb_strlen($property->getValue($object));
                }
                $parentReflectionObject = $property->getDeclaringClass()->getParentClass();
                while ($parentReflectionObject) {
                    if ($parentReflectionObject->hasProperty($property->getName())) {
                        $overriddenMethodHtml = sprintf(
                            '\\%s%s$%s',
                            $parentReflectionObject->getName(),
                            ($property->isStatic() ? "::" : "->"),
                            $property->getName()
                        );
                        if (!$commentHtmlInner) {
                            $commentHtmlInner = "";
                        }
                        $commentHtmlInner .= " ";
                        $commentHtmlInner .= sprintf(
                            '<span class="syntax--keyword syntax--other syntax--phpdoc syntax--php">@override</span> %s',
                            $overriddenMethodHtml
                        );
                        break;
                    }
                    $parentReflectionObject = $parentReflectionObject->getParentClass();
                }
                if ($commentHtmlInner) {
                    $html .= sprintf(
                        ' <span class="syntax--comment syntax--line syntax--double-slash syntax--php">// %s</span>',
                        $commentHtmlInner
                    );
                }
                return $html;
            };
            foreach ($properties as $property) {
                if ($className == $property->getDeclaringClass()->getName()) {
                    if ($property->isDefault()) {
                        $propertiesDeclaredInClass[] = $property;
                    } else {
                        $propertiesDeclaredAtRuntime[] = $property;
                    }
                } else {
                    $propertiesInherited[] = $property;
                }
            }
            if ($propertiesDeclaredInClass) {
                $innerHtml[] = $indentationInner . sprintf(
                    '<span class="syntax--comment syntax--line syntax--double-slash syntax--php">%s</span>',
                    "// Variables - declared in class"
                );
                foreach ($propertiesDeclaredInClass as $property) {
                    $innerHtml[] = $renderProperty($property);
                }
            }
            if ($propertiesInherited) {
                $innerHtml[] = $indentationInner . sprintf(
                    '<span class="syntax--comment syntax--line syntax--double-slash syntax--php">%s</span>',
                    "// Variables - inherited"
                );
                foreach ($propertiesInherited as $property) {
                    $inheritHtml = sprintf(
                        ' <span class="syntax--comment syntax--line syntax--double-slash syntax--php">// <span class="syntax--keyword syntax--other syntax--phpdoc syntax--php">@inherit</span> \\%s</span>',
                        $property->getDeclaringClass()->getName()
                    );
                    $innerHtml[] = $renderProperty($property) . $inheritHtml;
                }
            }
            if ($propertiesDeclaredAtRuntime) {
                $innerHtml[] = $indentationInner . sprintf(
                    '<span class="syntax--comment syntax--line syntax--double-slash syntax--php">%s</span>',
                    "// Variables - declared at runtime (injected)"
                );
                foreach ($propertiesDeclaredAtRuntime as $property) {
                    $innerHtml[] = $renderProperty($property);
                }
            }
        }

        // Methods
        $methods = $reflectionObject->getMethods();
        if ($methods) {
            $methodsDeclaredInClass = [];
            $methodsInherited = [];
            $renderMethod = function(\ReflectionMethod $method) use (&$self, $level, $indentationInner){
                $method->setAccessible(true);
                $storage = [];
                if ($method->isFinal()) {
                    $storage[] = "final";
                }
                $storage = [];
                if ($method->isAbstract()) {
                    $storage[] = "abstract";
                }
                if ($method->isPrivate()) {
                    $storage[] = "private";
                } elseif ($method->isProtected()) {
                    $storage[] = "protected";
                } elseif ($method->isPublic()) {
                    $storage[] = "public";
                }
                if ($method->isStatic()) {
                    $storage[] = "static";
                }
                $storage[] = "function";
                $storage = implode(' ', $storage);
                $parametersText = [];
                $parametersHtml = [];
                if ($method->getParameters()) {
                    foreach ($method->getParameters() as $parameter) {
                        $text = '';
                        $html = '';
                        if ($parameter->isCallable() || $parameter->isArray()) {
                            $type = "array";
                            if ($parameter->isCallable()) {
                                $type = "callable";
                            }
                            $text .= $type;
                            $html .= sprintf(
                                '<span class="syntax--storage">%s</span>',
                                $type
                            );
                        } else {
                            preg_match('/([\w][\w\\\\]+) \$\w+ \]/', (string)$parameter, $match);
                            if ($match) {
                                $text .= $match[1];
                                $html .= sprintf(
                                    '<span class="syntax--support class">\\%s</span>',
                                    htmlentities($match[1])
                                );
                            }
                        }
                        $text .= ' ';
                        $html .= ' ';
                        if ($parameter->isPassedByReference()) {
                            $text .= "&";
                            $html .= '<span class="syntax--storage">&amp;</span>';
                        }
                        $text .= $parameter->getName();
                        $html .= sprintf(
                            '<span class="syntax--variable">$%s</span>',
                            htmlentities($parameter->getName())
                        );
                        if ($parameter->isDefaultValueAvailable()) {
                            $text .= ' = ';
                            $html .= ' <span class="syntax--keyword syntax--operator syntax--assignment syntax--php">=</span> ';
                            if ($parameter->isDefaultValueConstant()) {
                                @list($class, $constant) = @explode('::', $parameter->getDefaultValueConstantName());
                                if (!$constant) {
                                    $constant = $class;
                                    $class = null;
                                }
                                if ($class) {
                                    $text .= "\\{$class}";
                                    $html .= sprintf(
                                        '<span class="syntax--support syntax--class">\\%s</span>::',
                                        htmlentities($class)
                                    );
                                }
                                $text .= $constant;
                                $html .= sprintf(
                                    '<span class="syntax--constant">%s</span>',
                                    htmlentities($constant)
                                );
                            } else {
                                $text .= $parameter->getDefaultValue();
                                $html .= $self->renderScalarOrNull($parameter->getDefaultValue());
                            }
                        }
                        $parametersText[] = $text;
                        $parametersHtml[] = ltrim($html);
                    }
                }
                $indentationInnerText = static::INDENTATION_CHARACTER_COUNT * ($level + 1);
                $substrText = $indentationInnerText . $storage . " " . implode(", ", $parametersText);
                if (mb_strlen($substrText) > self::PSR_2_SOFT_CHARACTER_LIMIT) {
                    $parametersHtml = PHP_EOL . implode("," . PHP_EOL, array_map(function($html) use ($indentationInner){
                        return $indentationInner . $this->generateIndentationForLevel(1) . ltrim($html);
                    }, $parametersHtml)) . PHP_EOL . $indentationInner;
                } else {
                    $parametersHtml = implode(", ", $parametersHtml);
                }
                $commentHtml = '';
                $parentReflectionObject = $method->getDeclaringClass()->getParentClass();
                while ($parentReflectionObject) {
                    if ($parentReflectionObject->hasMethod($method->getName())) {
                        $overriddenMethodHtml = sprintf(
                            '\\%s%s%s()',
                            $parentReflectionObject->getName(),
                            ($method->isStatic() ? "::" : "->"),
                            $method->getName()
                        );
                        $commentHtml = sprintf(
                            ' <span class="syntax--comment syntax--line syntax--double-slash syntax--php">// <span class="syntax--keyword syntax--other syntax--phpdoc syntax--php">@override</span> %s</span>',
                            $overriddenMethodHtml
                        );
                        break;
                    }
                    $parentReflectionObject = $parentReflectionObject->getParentClass();
                }
                $html = '';
                $html .= $indentationInner;
                $html .= sprintf(
                    '<span class="syntax--storage">%s</span> <span class="syntax--entity syntax--name function">%s</span>(%s);%s',
                    $storage,
                    $method->getName(),
                    $parametersHtml,
                    $commentHtml
                );
                return $html;
            };
            foreach ($methods as $method) {
                $method->setAccessible(true);
                $reflectionObjectDeclaringClass = $method->getDeclaringClass();
                if ($reflectionObjectDeclaringClass && $className == $reflectionObjectDeclaringClass->getName()) {
                    $methodsDeclaredInClass[] = $method;
                } else {
                    $methodsInherited[] = $method;
                }
            }
            if ($methodsDeclaredInClass) {
                $innerHtml[] = $indentationInner . sprintf(
                    '<span class="syntax--comment syntax--line syntax--double-slash syntax--php">%s</span>',
                    "// Methods - declared in class"
                );
                foreach ($methodsDeclaredInClass as $method) {
                    $innerHtml[] = $renderMethod($method);
                }
            }
            if ($methodsInherited) {
                $innerHtml[] = $indentationInner . sprintf(
                    '<span class="syntax--comment syntax--line syntax--double-slash syntax--php">%s</span>',
                    "// Methods - Inherited"
                );
                foreach ($methodsInherited as $method) {
                    $inheritHtml = sprintf(
                        ' <span class="syntax--comment syntax--line syntax--double-slash syntax--php">// <span class="syntax--keyword syntax--other syntax--phpdoc syntax--php">@inherit</span> \\%s</span>',
                        $method->getDeclaringClass()->getName()
                    );
                    $innerHtml[] = $renderMethod($method) . $inheritHtml;
                }
            }
        }
        $innerHtml = implode(PHP_EOL, $innerHtml);
        $html = '<span>'
            . '<span>' . $entityBlockHtml . '</span>'
            . '<span>' . $innerHtml . '</span>'
            . PHP_EOL . $indentation
            . '<span>}</span>'
            . '</span>';
        if ($level > 0) {
            $html = '<span class="collapsible expanded">' . $html . '</span>';
        }
        return $html;
    }

    private function renderObjectOmitted($object)
    {
        $hash = spl_object_hash($object);
        return sprintf(
            '<span title="Object #%s">(Object #%s; omitted)</span>',
            $hash,
            $hash
        );
    }

    private function renderObjectRecursion($object, $level = 0)
    {
        $hash = spl_object_hash($object);
        return sprintf(
            '<a href="#%s"><strong>**RECURSION**</strong> Object #%s</a>',
            "Object_{$hash}",
            htmlentities($hash)
        );
    }

    private function renderResource($resource)
    {
        return sprintf(
            'Resource #%d; <span class="syntax--comment syntax--line syntax--double-slash syntax--php">// Type: %s</span>',
            intval($resource),
            get_resource_type($resource)
        );
    }

    private function styleArrayToString(array $style)
    {
        $array = [];
        ksort($array);
        foreach ($style as $k => $v) {
            $array[] = "{$k}:$v";
        }
        if ($array) {
            return implode(';', $array) . ";";
        }
        return "";
    }
}
