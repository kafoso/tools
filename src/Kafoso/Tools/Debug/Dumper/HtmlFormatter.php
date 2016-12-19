<?php
namespace Kafoso\Tools\Debug\Dumper;

class HtmlFormatter extends AbstractFormatter
{
    const INDENTATION_CHARACTER = "·";
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
        $optionsHtml = ''
            . '<div class="optionsButton"></div>'
            . '<div class="options">'
                . '<dl>'
                    . '<dt>Collapse level:</dt>'
                    . '<dd>'
                        . '<input type="number" placeholder="Default: None" class="collapseLevel">'
                    . '</dd>'
                . '</dl>'
            . '</div>';
        return sprintf(
            '<div id="%s">'
                . '<style type="text/css">%s</style>'
                . $optionsHtml
                . '<pre>'
                . '<div class="wrap-guide">%s</div>'
                . '<div style="%s">%s</div>'
                . '</pre>'
                . '<script type="text/javascript">%s</script>'
                . '</div>',
            "Kafoso_Tools_Debug_Dumper_1a83b742_c5ce_11e6_9c64_842b2bb76d27",
            htmlentities($this->getCss()),
            str_repeat(" ", self::PSR_2_SOFT_CHARACTER_LIMIT),
            $this->styleArrayToString([
                "position" => "relative",
                "z-index" => 2,
            ]),
            $this->renderInner(),
            $this->getJavascript()
        );
    }

    public function renderInner()
    {
        return $this->prepareRecursively($this->var, $this->depth, 0, []);
    }

    public function getCss()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat("/..", 5));
        $css = file_get_contents($baseDirectory . "/resources/Kafoso/Tools/Debug/Dumper/HtmlFormatter/theme/dark-one-ui.css");
        return $css;
    }

    public function getJavascript()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat("/..", 5));
        $js = file_get_contents($baseDirectory . "/resources/Kafoso/Tools/Debug/Dumper/HtmlFormatter/js/main.js");
        return $js;
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
        return $indentation . $this->renderDefault($var);
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
                $html .= $this->renderDefault($k);
                $html .= ' => ';
                $html .= $this->prepareRecursively($v, ($depth-1), ($level+1), $previousSplObjectHashes, false);
                $html .= ",";
                $html .= PHP_EOL;
            }
            $html .= $indentation . ']';
        } else {
            $html = '[]';
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

    private function renderDefault($value)
    {
        $commentHtml = null;
        $class = [];
        if (is_bool($value) || is_null($value) || is_float($value) || is_int($value)) {
            $class[] = "constant";
            if (is_float($value) || is_int($value)) {
                $class[] = "numeric";
            }
        } elseif (is_string($value)) {
            $class[] = "string";
            $value = '"' . $value . '"';
        }
        if (is_null($value)) {
            $value = "null";
        } elseif (is_bool($value)) {
            $value = ($value ? "true" : "false");
        } else {
            $value = strval($value);
        }
        return sprintf(
            '<span class="%s">%s</span>',
            htmlentities(implode(" ", $class)),
            htmlentities($value)
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
            '<span class="storage">%s</span> <a title="%s" name="%s"><span class="entity name class">\\%s</span></a>',
            $entity,
            htmlentities("Object #{$hash}"),
            "Object_{$hash}",
            $className
        );
        $entityBlockText = "{$indentation}{$entity} {$className}";
        if ($reflectionObject->getParentClass()) {
            $extendsText = " extends " . $reflectionObject->getParentClass()->getName();
            $substrLength = mb_strlen($entityBlockText)+mb_strlen($extendsText)+strlen(" {");
            if ($substrLength > self::PSR_2_SOFT_CHARACTER_LIMIT) {
                $entityBlockText .= PHP_EOL . $indentationInner;
                $entityBlockHtml .= PHP_EOL . $indentationInner;
            } else {
                $entityBlockText .= " ";
                $entityBlockHtml .= " ";
            }
            $entityBlockText .= "extends " . $reflectionObject->getParentClass()->getName();
            $entityBlockHtml .= sprintf(
                '<span class="storage">extends</span> <span class="entity other inherited-class php">\\%s</span>',
                htmlentities($reflectionObject->getParentClass()->getName())
            );
        }
        if ($reflectionObject->getInterfaces()) {
            $interfaceNamesText = [];
            $interfaceNamesHtml = [];
            foreach ($reflectionObject->getInterfaces() as $interface) {
                $interfaceNamesText[] = $interface->getName();
                $interfaceNamesHtml[] = sprintf(
                    '<span class="entity other inherited-class php">\\%s</span>',
                    htmlentities($interface->getName())
                );
            }
            $entityBlockTextArray = explode(PHP_EOL, $entityBlockText);
            $entityBlockTextLastLine = end($entityBlockTextArray);
            $implementsText = " implements " . implode(", ", $interfaceNamesText);
            $substrLength = mb_strlen($entityBlockTextLastLine)+mb_strlen($implementsText)+strlen(" {");
            if ($substrLength > self::PSR_2_SOFT_CHARACTER_LIMIT) {
                $entityBlockText .= PHP_EOL . $indentationInner;
                $entityBlockHtml .= PHP_EOL . $indentationInner;
            } else {
                $entityBlockText .= " ";
                $entityBlockHtml .= " ";
            }
            $entityBlockText .= "implements " . $interface->getName();
            $entityBlockHtml .= sprintf(
                '<span class="storage">implements</span> %s',
                implode(", ", $interfaceNamesHtml)
            );
        }
        $entityBlockHtml .= ' {' . PHP_EOL;

        $innerHtml = [];

        // Traits
        if ($reflectionObject->getTraits()) {
            $innerHtml[] = $indentationInner . sprintf(
                '<span class="comment line double-slash php">%s</span>',
                "// Traits"
            );
            foreach ($reflectionObject->getTraits() as $trait) {
                $innerHtml[] = $indentationInner . sprintf(
                    '<span class="keyword other use php">use</span> <span class="support other namespace use php">\\%s</span>;',
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
                        ' <span class="comment line double-slash php">// Length: %d</span>',
                        mb_strlen($value)
                    );
                }
                return $indentationInner . sprintf(
                    '<span class="storage">const</span> <span class="constant">%s</span> = %s;',
                    htmlentities($name),
                    $this->renderDefault($value)
                ) . $commentHtml;
            };
            $innerHtml[] = $indentationInner . sprintf(
                '<span class="comment line double-slash php">%s</span>',
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
                    '<span class="storage">%s</span> <span class="variable">$%s</span>',
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
                            '<span class="keyword">@override</span> %s',
                            $overriddenMethodHtml
                        );
                        break;
                    }
                    $parentReflectionObject = $parentReflectionObject->getParentClass();
                }
                if ($commentHtmlInner) {
                    $html .= sprintf(
                        ' <span class="comment line double-slash php">// %s</span>',
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
                    '<span class="comment line double-slash php">%s</span>',
                    "// Variables - declared in class"
                );
                foreach ($propertiesDeclaredInClass as $property) {
                    $innerHtml[] = $renderProperty($property);
                }
            }
            if ($propertiesInherited) {
                $innerHtml[] = $indentationInner . sprintf(
                    '<span class="comment line double-slash php">%s</span>',
                    "// Variables - inherited"
                );
                foreach ($propertiesInherited as $property) {
                    $inheritHtml = sprintf(
                        ' <span class="comment line double-slash php">// <span class="keyword">@inherit</span> \\%s</span>',
                        $property->getDeclaringClass()->getName()
                    );
                    $innerHtml[] = $renderProperty($property) . $inheritHtml;
                }
            }
            if ($propertiesDeclaredAtRuntime) {
                $innerHtml[] = $indentationInner . sprintf(
                    '<span class="comment line double-slash php">%s</span>',
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
                                '<span class="storage">%s</span>',
                                $type
                            );
                        } else {
                            preg_match('/([\w][\w\\\\]+) \$\w+ \]/', (string)$parameter, $match);
                            if ($match) {
                                $text .= $match[1];
                                $html .= sprintf(
                                    '<span class="support class">\\%s</span>',
                                    htmlentities($match[1])
                                );
                            }
                        }
                        $text .= ' ';
                        $html .= ' ';
                        if ($parameter->isPassedByReference()) {
                            $text .= "&";
                            $html .= '<span class="storage">&amp;</span>';
                        }
                        $text .= $parameter->getName();
                        $html .= sprintf(
                            '<span class="variable">$%s</span>',
                            htmlentities($parameter->getName())
                        );
                        if ($parameter->isDefaultValueAvailable()) {
                            $text .= ' = ';
                            $html .= ' <span class="keyword operator">=</span> ';
                            if ($parameter->isDefaultValueConstant()) {
                                @list($class, $constant) = @explode('::', $parameter->getDefaultValueConstantName());
                                if (!$constant) {
                                    $constant = $class;
                                    $class = null;
                                }
                                if ($class) {
                                    $text .= "\\{$class}";
                                    $html .= sprintf(
                                        '<span class="support class">\\%s</span>::',
                                        htmlentities($class)
                                    );
                                }
                                $text .= $constant;
                                $html .= sprintf(
                                    '<span class="constant">%s</span>',
                                    htmlentities($constant)
                                );
                            } else {
                                $text .= $parameter->getDefaultValue();
                                $html .= $self->renderDefault($parameter->getDefaultValue());
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
                            ' <span class="comment line double-slash php">// <span class="keyword">@override</span> %s</span>',
                            $overriddenMethodHtml
                        );
                        break;
                    }
                    $parentReflectionObject = $parentReflectionObject->getParentClass();
                }
                $html = '';
                $html .= $indentationInner;
                $html .= sprintf(
                    '<span class="storage">%s</span> <span class="entity name function">%s</span>(%s);%s',
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
                    '<span class="comment line double-slash php">%s</span>',
                    "// Methods - declared in class"
                );
                foreach ($methodsDeclaredInClass as $method) {
                    $innerHtml[] = $renderMethod($method);
                }
            }
            if ($methodsInherited) {
                $innerHtml[] = $indentationInner . sprintf(
                    '<span class="comment line double-slash php">%s</span>',
                    "// Methods - Inherited"
                );
                foreach ($methodsInherited as $method) {
                    $inheritHtml = sprintf(
                        ' <span class="comment line double-slash php">// <span class="keyword">@inherit</span> \\%s</span>',
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
        $html = '<span class="expanded">' . $html . '</span>';
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
            'Resource #%d; <span class="comment line double-slash php">// Type: %s</span>',
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
