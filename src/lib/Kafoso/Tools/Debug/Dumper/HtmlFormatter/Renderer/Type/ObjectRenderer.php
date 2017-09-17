<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Configuration;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\AbstractRenderer;
use Kafoso\Tools\Exception\Formatter;
use Kafoso\Tools\Generic\HTML;

class ObjectRenderer extends AbstractRenderer
{
    protected $object;
    protected $level;
    protected $previousSplObjectHashes;

    /**
     * @param null|string $endingCharacter
     * @param object $object
     * @param int $level
     * @param array $previousSplObjectHashes
     */
    public function __construct(Configuration $configuration, $endingCharacter, $object, $level, array $previousSplObjectHashes)
    {
        $this->configuration = $configuration;
        $this->endingCharacter = $endingCharacter;
        $this->object = $object;
        $this->level = $level;
        $this->previousSplObjectHashes = $previousSplObjectHashes;
    }

    /**
     * @inheritDoc
     */
    public function getIntermediary()
    {
        $intermediary = $this->getIntermediaryWithClassDeclaration();

        if ($this->configuration->isTruncatingGenericObjects()) {
            if (in_array(get_class($this->object), $this->configuration::getTruncatedGenericClasses())) {
                switch (get_class($this->object)) {
                    case "Closure":
                        return $intermediary->merge((new ObjectRenderer\Truncated\ClosureRenderer(
                            $this->configuration,
                            ";",
                            $this->object,
                            $this->level,
                            []
                        ))->getIntermediary());
                    case "DateInterval":
                        return $intermediary->merge((new ObjectRenderer\Truncated\DateIntervalRenderer(
                            $this->configuration,
                            ";",
                            $this->object,
                            $this->level,
                            []
                        ))->getIntermediary());
                    case "DatePeriod":
                        return $intermediary->merge((new ObjectRenderer\Truncated\DatePeriodRenderer(
                            $this->configuration,
                            ";",
                            $this->object,
                            $this->level,
                            []
                        ))->getIntermediary());
                    case "DateTime":
                    case "DateTimeImmutable":
                        return $intermediary->merge((new ObjectRenderer\Truncated\DateTimeRenderer(
                            $this->configuration,
                            ";",
                            $this->object,
                            $this->level,
                            []
                        ))->getIntermediary());
                }
            }
        }

        $reflectionObject = new \ReflectionObject($this->object);

        $propertiesDeclaredInClass = [];
        $propertiesDeclaredAtRuntime = [];
        $propertiesInherited = [];
        $propertiesPrivateInParentClasses = [];
        $currentReflectionObject = $reflectionObject;
        $isFirst = true;
        do {
            if (false == $isFirst && get_class($this->object) == $currentReflectionObject->getName()) {
                // Prevent circular pattern
                break;
            }
            $properties = $currentReflectionObject->getProperties();
            if ($properties) {
                foreach ($properties as $property) {
                    $property->setAccessible(true);
                    if ($property->isDefault()) {
                        if (get_class($this->object) == $property->getDeclaringClass()->getName()) {
                            $propertiesDeclaredInClass[] = $property;
                        } else {
                            if ($property->isPrivate()) {
                                $propertiesPrivateInParentClasses[] = $property;
                            } else {
                                $propertiesInherited[] = $property;
                            }
                        }
                    } else {
                        $propertiesDeclaredAtRuntime[] = $property;
                    }
                }
            }
            $isFirst = false;
            $currentReflectionObject = $currentReflectionObject->getParentClass();
        } while ($currentReflectionObject);
        $hasProperties = (
            $propertiesDeclaredInClass
            || $propertiesDeclaredAtRuntime
            || $propertiesInherited
            || $propertiesPrivateInParentClasses
        );

        $methodsDeclaredInClass = [];
        $methodsInherited = [];
        foreach ($reflectionObject->getMethods() as $method) {
            $method->setAccessible(true);
            $reflectionObjectDeclaringClass = $method->getDeclaringClass();
            if ($reflectionObjectDeclaringClass) {
                if ($reflectionObject->getName() == $reflectionObjectDeclaringClass->getName()) {
                    $methodsDeclaredInClass[] = $method;
                } else {
                    $methodsInherited[] = $method;
                }
            }
        }
        $hasMethods = [
            $methodsDeclaredInClass
            || $methodsInherited
        ];

        // Extends + Interfaces
        $extendsIntermediary = new Intermediary;
        $currentParentReflection = $reflectionObject->getParentClass();
        if ($currentParentReflection) {
            $isFirst = true;
            $extendsIntermediary->addSegment(new Segment(" "));
            $extendsIntermediary->addSegment(new Segment('<span class="syntax--storage syntax--modifier syntax--extends">', true));
            $extendsIntermediary->addSegment(new Segment("extends"));
            $extendsIntermediary->addSegment(new Segment('</span>', true));
            $extendsIntermediary->addSegment(new Segment(" "));
            while ($currentParentReflection) {
                if (false == $isFirst) {
                    $extendsIntermediary->addSegment(new Segment(", "));
                }
                $extendsIntermediary->addSegment(new Segment('<span class="syntax--entity syntax--other syntax--inherited-class">', true));
                $extendsIntermediary->addSegment(new Segment("\\" . $currentParentReflection->getName()));
                $extendsIntermediary->addSegment(new Segment('</span>', true));
                $isFirst = false;
                $currentParentReflection = $currentParentReflection->getParentClass();
            }
        }
        $interfacesIntermediary = new Intermediary;
        if ($reflectionObject->getInterfaces()) {
            $interfacesIntermediary->addSegment(new Segment(" "));
            $interfacesIntermediary->addSegment(new Segment('<span class="syntax--storage syntax--modifier syntax--implements">', true));
            $interfacesIntermediary->addSegment(new Segment("implements"));
            $interfacesIntermediary->addSegment(new Segment('</span>', true));
            $interfacesIntermediary->addSegment(new Segment(" "));
            $isFirst = true;
            foreach ($reflectionObject->getInterfaces() as $interface) {
                if (false == $isFirst) {
                    $interfacesIntermediary->addSegment(new Segment(", "));
                }
                $interfacesIntermediary->addSegment(new Segment('<span class="syntax--meta syntax--other syntax--inherited-class">', true));
                $interfacesIntermediary->addSegment(new Segment('<span class="syntax--entity syntax--other syntax--inherited-class">', true));
                $interfacesIntermediary->addSegment(new Segment("\\" . $interface->getName()));
                $interfacesIntermediary->addSegment(new Segment('</span>', true));
                $interfacesIntermediary->addSegment(new Segment('</span>', true));
                $isFirst = false;
            }
        }
        $totalLength = $intermediary->getStringLengthOfSegments(false)
            + $extendsIntermediary->getStringLengthOfSegments(false)
            + $interfacesIntermediary->getStringLengthOfSegments(false);
        if ($totalLength > HtmlFormatter::PSR_2_SOFT_CHARACTER_LIMIT) {
            if ($extendsIntermediary->hasSegments()) {
                $intermediary->addSegment(new Segment('<span class="section--extends">', true));
                $intermediary->addSegment(new Segment(PHP_EOL));
                $this->indentIntermediary($intermediary, ($this->level+1));
                $isFirst = true;
                foreach ($extendsIntermediary->getSegments() as $segment) {
                    if ($isFirst) {
                        $isFirst = false;
                        continue; // Disregard first space
                    }
                    $intermediary->addSegment($segment);
                    if (", " == $segment->getString()) {
                        $intermediary->addSegment(new Segment(PHP_EOL));
                        $this->indentIntermediary($intermediary, ($this->level+2));
                    }
                }
                $intermediary->addSegment(new Segment('</span>', true));
            }
            if ($interfacesIntermediary->hasSegments()) {
                $intermediary->addSegment(new Segment('<span class="section--implements">', true));
                $intermediary->addSegment(new Segment(PHP_EOL));
                $this->indentIntermediary($intermediary, ($this->level+1));
                $isFirst = true;
                foreach ($interfacesIntermediary->getSegments() as $segment) {
                    if ($isFirst) {
                        $isFirst = false;
                        continue; // Disregard first space
                    }
                    $intermediary->addSegment($segment);
                    if (", " == $segment->getString()) {
                        $intermediary->addSegment(new Segment(PHP_EOL));
                        $this->indentIntermediary($intermediary, ($this->level+2));
                    }
                }
                $intermediary->addSegment(new Segment('</span>', true));
            }
        } else {
            if ($extendsIntermediary->hasSegments()) {
                $intermediary->addSegment(new Segment('<span class="section--extends">', true));
                $intermediary->merge($extendsIntermediary);
                $intermediary->addSegment(new Segment('</span>', true));
            }
            if ($interfacesIntermediary->hasSegments()) {
                $intermediary->addSegment(new Segment('<span class="section--implements">', true));
                $intermediary->merge($interfacesIntermediary);
                $intermediary->addSegment(new Segment('</span>', true));
            }
        }

        $intermediary->addSegment(new Segment(PHP_EOL));
        $this->indentIntermediary($intermediary, $this->level);
        $intermediary->addSegment(new Segment('{'));
        $intermediary->addSegment(new Segment(PHP_EOL));

        // Traits
        if ($reflectionObject->getTraits()) {
            $intermediary->addSegment(new Segment('<span class="section--traits">', true));
            $this->indentIntermediary($intermediary, ($this->level+1));
            $intermediary->addSegment(new Segment('<span class="syntax--comment syntax--line syntax--double-slash">', true));
            $intermediary->addSegment(new Segment('// Traits (' . count($reflectionObject->getTraits()) . ')'));
            $intermediary->addSegment(new Segment('</span>', true));
            $intermediary->addSegment(new Segment(PHP_EOL));

            foreach ($reflectionObject->getTraits() as $trait) {
                $this->indentIntermediary($intermediary, ($this->level+1));
                $intermediary->addSegment(new Segment('<span class="syntax--keyword syntax--other syntax--use">', true));
                $intermediary->addSegment(new Segment('use'));
                $intermediary->addSegment(new Segment('</span>', true));
                $intermediary->addSegment(new Segment(' '));
                $intermediary->addSegment(new Segment('<span class="syntax--support syntax--other syntax--namespace syntax--use">', true));
                $intermediary->addSegment(new Segment('\\' . $trait->getName()));
                $intermediary->addSegment(new Segment('</span>', true));
                $intermediary->addSegment(new Segment(';'));
                $intermediary->addSegment(new Segment(PHP_EOL));
            }

            $intermediary->addSegment(new Segment('<span class="super-section--spacing">', true));
            $intermediary->addSegment(new Segment(PHP_EOL));
            $intermediary->addSegment(new Segment('</span>', true));

            $intermediary->addSegment(new Segment('</span>', true));
        }

        // Constants
        if ($reflectionObject->getConstants()) {
            $intermediary->addSegment(new Segment('<span class="section--constants">', true));
            $this->indentIntermediary($intermediary, ($this->level+1));
            $intermediary->addSegment(new Segment('<span class="syntax--comment syntax--line syntax--double-slash">', true));
            $intermediary->addSegment(new Segment('// Constants (' . count($reflectionObject->getConstants()) . ')'));
            $intermediary->addSegment(new Segment('</span>', true));
            $intermediary->addSegment(new Segment(PHP_EOL));
            foreach ($reflectionObject->getConstants() as $name => $value) {
                $this->indentIntermediary($intermediary, ($this->level+1));
                $intermediary->addSegment(new Segment('<span class="syntax--storage">const</span> <span class="syntax--constant">', true));
                $intermediary->addSegment(new Segment($name));
                $intermediary->addSegment(new Segment('</span>', true));
                $intermediary->addSegment(new Segment(' = '));
                $intermediary->merge($this->generateIntermediaryBasedOnDataType($value));
                $intermediary->addSegment(new Segment(PHP_EOL));
            }

            $intermediary->addSegment(new Segment('<span class="super-section--spacing">', true));
            $intermediary->addSegment(new Segment(PHP_EOL));
            $intermediary->addSegment(new Segment('</span>', true));

            $intermediary->addSegment(new Segment('</span>', true));
        }

        // Variables
        if ($hasProperties) {
            if ($propertiesDeclaredInClass) {
                $intermediary->addSegment(new Segment('<span class="section--properties">', true));
                $this->_handleProperties(
                    $intermediary,
                    "Variables - Declared in class",
                    $propertiesDeclaredInClass
                );

                $intermediary->addSegment(new Segment('<span class="super-section--spacing">', true));
                $intermediary->addSegment(new Segment(PHP_EOL));
                $intermediary->addSegment(new Segment('</span>', true));

                $intermediary->addSegment(new Segment('</span>', true));
            }
            if ($propertiesInherited) {
                $intermediary->addSegment(new Segment('<span class="section--properties">', true));
                $this->_handleProperties(
                    $intermediary,
                    "Variables - Inherited",
                    $propertiesInherited
                );
                $intermediary->addSegment(new Segment('<span class="super-section--spacing">', true));
                $intermediary->addSegment(new Segment(PHP_EOL));
                $intermediary->addSegment(new Segment('</span>', true));

                $intermediary->addSegment(new Segment('</span>', true));
            }
            if ($propertiesPrivateInParentClasses) {
                $intermediary->addSegment(new Segment('<span class="section--properties">', true));
                $this->_handleProperties(
                    $intermediary,
                    "Variables - Private in parent class(es)",
                    $propertiesPrivateInParentClasses
                );
                $intermediary->addSegment(new Segment('<span class="super-section--spacing">', true));
                $intermediary->addSegment(new Segment(PHP_EOL));
                $intermediary->addSegment(new Segment('</span>', true));

                $intermediary->addSegment(new Segment('</span>', true));
            }
            if ($propertiesDeclaredAtRuntime) {
                $intermediary->addSegment(new Segment('<span class="section--properties">', true));
                $this->_handleProperties(
                    $intermediary,
                    "Variables - Declared at runtime (injected)",
                    $propertiesDeclaredAtRuntime
                );
                $intermediary->addSegment(new Segment('<span class="super-section--spacing">', true));
                $intermediary->addSegment(new Segment(PHP_EOL));
                $intermediary->addSegment(new Segment('</span>', true));

                $intermediary->addSegment(new Segment('</span>', true));
            }
        }

        // Methods
        if ($hasMethods) {
            if ($methodsDeclaredInClass) {
                $intermediary->addSegment(new Segment('<span class="section--methods">', true));
                $this->_handleMethods(
                    $intermediary,
                    $reflectionObject,
                    "// Methods - Declared in class",
                    $methodsDeclaredInClass
                );

                $intermediary->addSegment(new Segment('<span class="super-section--spacing">', true));
                $intermediary->addSegment(new Segment(PHP_EOL));
                $intermediary->addSegment(new Segment('</span>', true));

                $intermediary->addSegment(new Segment('</span>', true));
            }
            if ($methodsInherited) {
                $intermediary->addSegment(new Segment('<span class="section--methods">', true));
                $this->_handleMethods(
                    $intermediary,
                    $reflectionObject,
                    "// Methods - Inherited",
                    $methodsInherited
                );

                $intermediary->addSegment(new Segment('<span class="super-section--spacing">', true));
                $intermediary->addSegment(new Segment(PHP_EOL));
                $intermediary->addSegment(new Segment('</span>', true));

                $intermediary->addSegment(new Segment('</span>', true));
            }
        }

        $this->indentIntermediary($intermediary, $this->level);
        $intermediary->addSegment(new Segment('}'));

        return $intermediary;
    }

    /**
     * @return Intermediary
     */
    protected function getIntermediaryWithClassDeclaration()
    {
        $intermediary = new Intermediary;
        $intermediary->addSegment(new Segment('<span class="syntax--storage syntax--type syntax--class">', true));

        $hash = spl_object_hash($this->object);
        $reflectionObject = new \ReflectionObject($this->object);

        $syntaxClass = [];
        if ($reflectionObject->isFinal()) {
            $syntaxClass[] = "final";
        }
        if ($reflectionObject->isAbstract()) {
            $syntaxClass[] = "abstract";
        }
        if ($reflectionObject->isTrait()) {
            $syntaxClass[] = "trait";
        } else {
            $syntaxClass[] = "class";
        }
        $intermediary->addSegment(new Segment(implode(" ", $syntaxClass)));
        $intermediary->addSegment(new Segment('</span>', true));
        $intermediary->addSegment(new Segment(" "));
        $intermediary->addSegment(new Segment(sprintf(
            '<span title="%s" data-object="%s" class="syntax--entity syntax--name syntax--class">',
            HTML::encode("Object #{$hash}"),
            HTML::encode("{$hash}")
        ), true));
        $intermediary->addSegment(new Segment("\\" . get_class($this->object)));
        $intermediary->addSegment(new Segment('</span>', true));
        return $intermediary;
    }

    /**
     * @param string $commentText
     * @param array $methods                    An array of \ReflectionMethod.
     * @return void
     */
    private function _handleMethods(
        Intermediary $intermediary,
        \ReflectionObject $reflectionObject,
        $commentText,
        array $methods
    )
    {
        $this->indentIntermediary($intermediary, ($this->level+1));
        $intermediary->addSegment(new Segment('<span class="syntax--comment syntax--line syntax--double-slash">', true));
        $intermediary->addSegment(new Segment($commentText . ' (' . count($methods) . ')'));
        $intermediary->addSegment(new Segment('</span>', true));
        $intermediary->addSegment(new Segment(PHP_EOL));

        foreach ($methods as $method) {
            $intermediary->merge((new ObjectRenderer\MethodRenderer(
                $this->configuration,
                ";",
                $method,
                $reflectionObject,
                ($this->level+1)
            ))->getIntermediary());
        }
    }

    /**
     * @param string $commentText
     * @param array $properties                 An array of \ReflectionProperty.
     * @return void
     */
    private function _handleProperties(
        Intermediary $intermediary,
        $commentText,
        array $properties
    )
    {
        $this->indentIntermediary($intermediary, ($this->level+1));
        $intermediary->addSegment(new Segment('<span class="syntax--comment syntax--line syntax--double-slash">', true));
        $intermediary->addSegment(new Segment('// ' . $commentText . ' (' . count($properties) . ')'));
        $intermediary->addSegment(new Segment('</span>', true));
        $intermediary->addSegment(new Segment(PHP_EOL));
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $intermediary->merge((new ObjectRenderer\PropertyRenderer(
                $this->configuration,
                ";",
                $property,
                $property->getValue($this->object),
                ($this->level+1),
                $this->previousSplObjectHashes
            ))->getIntermediary());
            $intermediary->addSegment(new Segment(PHP_EOL));
        }
    }
}
