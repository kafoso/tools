<?php
namespace Kafoso\Tools\Debug\Dumper;

class HtmlFormatter extends AbstractFormatter
{
    public function render()
    {
        $style = [
            "background-color" => "rgb(40,44,52)",
            "color" => "#abb2bf",
            "font-family" => "Menlo,Consolas,'DejaVu Sans Mono',monospace",
            "overflow" => "hidden",
            "padding" => "10px",
            "position" => "relative",
        ];
        return sprintf(
            '<div style="%s">%s</div>',
            $this->styleArrayToString($style),
            $this->renderInner()
        );
    }

    public function renderInner()
    {
        return $this->prepareRecursively($this->var, $this->depth, 0, []);
    }

    private function prepareRecursively($var, $depth, $level, array $previousSplObjectHashes)
    {
        $indentation = str_repeat(self::INDENTATION_CHARACTERS, $level);
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
            return $this->renderArray($var, $depth, $level, $previousSplObjectHashes);
        } elseif (is_resource($var)) {
            return $indentation . $this->renderResource($var);
        }
        return $indentation . $this->renderDefault($var);
    }

    public function renderArray(array $array, $depth, $level, array $previousSplObjectHashes)
    {
        if ($array) {
            $indentation = str_repeat(self::INDENTATION_CHARACTERS, $level);
            $html = $indentation . '[' . PHP_EOL;
            $level += 1;
            $indentationInner = str_repeat(self::INDENTATION_CHARACTERS, $level);
            foreach ($array as $k => $v) {
                $html .= $indentationInner;
                $html .= $this->renderDefault($k);
                $html .= ' => ';
                $html .= $this->prepareRecursively($v, ($depth-1), 0, $previousSplObjectHashes);
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
        $arraySize = count($array);
    }

    private function renderDefault($value)
    {
        $style = [];
        if (is_bool($value) || is_null($value) || is_numeric($value)) {
            $style["color"] = "#d19a66";
        } elseif (is_string($value)) {
            $style["color"] = "#98c379";
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
            '<span style="%s">%s</span>',
            htmlentities($this->styleArrayToString($style)),
            htmlentities($value)
        );
    }

    private function renderObject($object, $depth, $level, array $previousSplObjectHashes)
    {
        $hash = spl_object_hash($object);
        $reflection = new \ReflectionObject($object);
        $indentation = str_repeat(self::INDENTATION_CHARACTERS, ($level+1));
        $innerHtml = [];
        $properties = $reflection->getProperties();
        $className = get_class($object);
        if ($properties) {
            $innerHtml[] = $indentation . sprintf(
                '<span style="%s">%s</span>',
                $this->styleArrayToString(["color" => "#5c6370"]),
                "// Variables"
            );
            foreach ($properties as $property) {
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
                $html .= $indentation;
                $html .= sprintf(
                    '<span style="%s">%s</span> <span style="%s">$%s</span>',
                    $this->styleArrayToString(["color" => "#c678dd"]),
                    $storage,
                    $this->styleArrayToString(["color" => "#e06c75"]),
                    $property->getName()
                );
                $html .= " = ";
                $html .= ltrim($this->prepareRecursively(
                    $property->getValue($object),
                    ($depth-1),
                    ($level+1),
                    $previousSplObjectHashes
                ));
                $html .= ";";
                $innerHtml[] = $html;
            }
        }
        $methods = $reflection->getMethods();
        if ($methods) {
            $methodsDeclaredInClass = [];
            $methodsInherited = [];
            $self = $this;
            $renderMethod = function(\ReflectionMethod $method) use (&$self, $indentation){
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
                $parametersHtml = [];
                if ($method->getParameters()) {
                    foreach ($method->getParameters() as $parameter) {
                        $h = '';
                        if ($parameter->hasType()) {
                            $h .= sprintf(
                                '<span style="%s">\\%s</span>',
                                $self->styleArrayToString(["color" => "#e5c07b"]),
                                htmlentities((string)$parameter->getType())
                            );
                        }
                        if ($parameter->isPassedByReference()) {
                            $h .= sprintf(
                                '<span style="%s">&amp;</span>',
                                $self->styleArrayToString(["color" => "#c678dd"])
                            );
                        }
                        $h .= sprintf(
                            '<span style="%s">$%s</span>',
                            $self->styleArrayToString(["color" => "#e06c75"]),
                            htmlentities($parameter->getName())
                        );
                        if ($parameter->isDefaultValueAvailable()) {
                            $h .= " = ";
                            if ($parameter->isDefaultValueConstant()) {
                                @list($class, $constant) = @explode('::', $parameter->getDefaultValueConstantName());
                                if (!$constant) {
                                    $constant = $class;
                                    $class = null;
                                }
                                if ($class) {
                                    $h .= sprintf(
                                        '<span style="%s">%s</span>::',
                                        $self->styleArrayToString(["color" => "#e5c07b"]),
                                        htmlentities($class)
                                    );
                                }
                                $h .= sprintf(
                                    '<span style="%s">%s</span>',
                                    $self->styleArrayToString(["color" => "#d19a66"]),
                                    htmlentities($constant)
                                );
                            } else {
                                $h .= $self->renderDefault($parameter->getDefaultValue());
                            }
                        }
                        $parametersHtml[] = $h;
                    }
                }
                $parametersHtml = implode(", ", $parametersHtml);
                $commentHtml = '';
                /*
                XXX Check if method overrides inherited method
                if (false) {
                    $commentHtml = sprintf(
                        '<span style="%s">// @override</span>',
                        $self->styleArrayToString(["color" => "#5c6370"])
                    );
                }
                */
                $html = '';
                $html .= $indentation;
                $html .= sprintf(
                    '<span style="%s">%s</span> <span style="%s">%s</span>(%s);%s',
                    $self->styleArrayToString(["color" => "#c678dd"]),
                    $storage,
                    $self->styleArrayToString(["color" => "#e06c75"]),
                    $method->getName(),
                    $parametersHtml,
                    $commentHtml
                );
                return $html;
            };
            foreach ($methods as $method) {
                $method->setAccessible(true);
                $reflectionDeclaringClass = $method->getDeclaringClass();
                if ($reflectionDeclaringClass && $className == $reflectionDeclaringClass->getName()) {
                    $methodsDeclaredInClass[] = $method;
                } else {
                    $methodsInherited[] = $method;
                }
            }
            if ($methodsDeclaredInClass) {
                $innerHtml[] = $indentation . sprintf(
                    '<span style="%s">%s</span>',
                    $this->styleArrayToString(["color" => "#5c6370"]),
                    "// Methods - declared in class"
                );
                foreach ($methodsDeclaredInClass as $method) {
                    $innerHtml[] = $renderMethod($method);
                }
            }
            if ($methodsInherited) {
                $innerHtml[] = $indentation . sprintf(
                    '<span style="%s">%s</span>',
                    $this->styleArrayToString(["color" => "#5c6370"]),
                    "// Methods - Inherited"
                );
                foreach ($methodsInherited as $method) {
                    $tmpHtml = '';
                    $tmpHtml .= $renderMethod($method);
                    $tmpHtml .= sprintf(
                        '<span style="%s">// Inherited from: \\%s</span>',
                        $this->styleArrayToString(["color" => "#5c6370"]),
                        $method->getDeclaringClass()->getName()
                    );
                    $innerHtml[] = $tmpHtml;
                }
            }
        }
        $innerHtml = implode(PHP_EOL, $innerHtml);
        $entity = [];
        if ($reflection->isFinal()) {
            $entity[] = "final";
        }
        if ($reflection->isAbstract()) {
            $entity[] = "abstract";
        }
        if ($reflection->isTrait()) {
            $entity[] = "trait";
        } else {
            $entity[] = "class";
        }
        $entityBlockHtml = '';
        $entity = implode(" ", $entity);
        $entityBlockHtml .= sprintf(
            '<div><span style="%s">%s</span> <span style="%s">\\%s</span> {</div>',
            $this->styleArrayToString(["color" => "#c678dd"]),
            $entity,
            $this->styleArrayToString(["color" => "#e5c07b"]),
            $className
        );
        return '<div>'
            . PHP_EOL
            . $entityBlockHtml
            . PHP_EOL
            . '<div>'
            . PHP_EOL
            . $innerHtml
            . PHP_EOL
            . '</div>'
            . PHP_EOL
            . '<div>}</div>'
            . PHP_EOL
            . '</div>';
    }

    private function renderObjectOmitted($object)
    {
        $hash = spl_object_hash($object);
    }

    private function renderObjectRecursion($object, $level = 0)
    {
        $hash = spl_object_hash($object);
    }

    private function renderResource($resource)
    {
        return sprintf(
            'Resource #%d <span style="%s">(type: %s)</span>',
            intval($resource),
            $this->styleArrayToString(["color" => "#5c6370"]),
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
