<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type\ObjectRenderer;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Configuration;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\AbstractRenderer;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type;
use Kafoso\Tools\Exception\Formatter;

class MethodRenderer extends AbstractRenderer
{
    private $reflectionMethod;
    private $owningReflectionObject;
    private $level;

    /**
     * @param int $level
     */
    public function __construct(
        Configuration $configuration,
        $endingCharacter,
        \ReflectionMethod $reflectionMethod,
        \ReflectionObject $owningReflectionObject,
        $level
    )
    {
        $this->configuration = $configuration;
        $this->endingCharacter = $endingCharacter;
        $this->reflectionMethod = $reflectionMethod;
        $this->owningReflectionObject = $owningReflectionObject;
        $this->level = $level;
    }

    public function getIntermediary()
    {
        $intermediary = new Intermediary;
        $this->indentIntermediary($intermediary, $this->level);
        $intermediary->addSegment(new Segment('<span class="syntax--storage syntax--modifier">', true));
        if ($this->reflectionMethod->isAbstract()) {
            $intermediary->addSegment(new Segment("abstract "));
        }
        if ($this->reflectionMethod->isPrivate()) {
            $intermediary->addSegment(new Segment("private "));
        } elseif ($this->reflectionMethod->isProtected()) {
            $intermediary->addSegment(new Segment("protected "));
        } elseif ($this->reflectionMethod->isPublic()) {
            $intermediary->addSegment(new Segment("public "));
        }
        if ($this->reflectionMethod->isStatic()) {
            $intermediary->addSegment(new Segment("static "));
        }
        $intermediary->addSegment(new Segment("function"));
        $intermediary->addSegment(new Segment('</span>', true));
        $intermediary->addSegment(new Segment(" "));
        if (self::isMethodNameMagic($this->reflectionMethod->getName())) {
            $intermediary->addSegment(new Segment('<span class="syntax--support syntax--function syntax--magic">', true));
        } else {
            $intermediary->addSegment(new Segment('<span class="syntax--entity syntax--name syntax--function">', true));
        }
        $intermediary->addSegment(new Segment($this->reflectionMethod->getName()));
        $intermediary->addSegment(new Segment('</span>', true));
        $intermediary->addSegment(new Segment('('));

        $intermediaryInheritOrOverride = new Intermediary;

        if ($this->owningReflectionObject->getName() != $this->reflectionMethod->getDeclaringClass()->getName()) {
            $intermediaryInheritOrOverride->addSegment(new Segment(" "));
            $intermediaryInheritOrOverride->addSegment(new Segment('<span class="syntax--comment syntax--line syntax--double-slash">', true));
            $intermediaryInheritOrOverride->addSegment(new Segment("// "));
            $intermediaryInheritOrOverride->addSegment(new Segment('<span class="syntax--keyword syntax--other syntax--phpdoc">', true));
            $intermediaryInheritOrOverride->addSegment(new Segment('@inherit'));
            $intermediaryInheritOrOverride->addSegment(new Segment('</span>', true));
            $intermediaryInheritOrOverride->addSegment(new Segment(' \\' . $this->reflectionMethod->getDeclaringClass()->getName()));
            $intermediaryInheritOrOverride->addSegment(new Segment('</span>', true));
        } else {
            $parentReflectionObject = $this->reflectionMethod->getDeclaringClass()->getParentClass();
            $isFirst = true;
            while ($parentReflectionObject) {
                if (false == $isFirst) {
                    if ($parentReflectionObject->getName() == $this->owningReflectionObject->getName()) {
                        break;
                    }
                }
                if ($parentReflectionObject->hasMethod($this->reflectionMethod->getName())) {
                    $intermediaryInheritOrOverride->addSegment(new Segment(" "));
                    $intermediaryInheritOrOverride->addSegment(new Segment('<span class="syntax--comment syntax--line syntax--double-slash">', true));
                    $intermediaryInheritOrOverride->addSegment(new Segment("// "));
                    $intermediaryInheritOrOverride->addSegment(new Segment('<span class="syntax--keyword syntax--other syntax--phpdoc">', true));
                    $intermediaryInheritOrOverride->addSegment(new Segment('@override'));
                    $intermediaryInheritOrOverride->addSegment(new Segment('</span>', true));
                    $intermediaryInheritOrOverride->addSegment(new Segment(' \\' . $parentReflectionObject->getName()));
                    if ($parentReflectionObject->getMethod($this->reflectionMethod->getName())->isStatic()) {
                        $intermediaryInheritOrOverride->addSegment(new Segment("::"));
                    } else {
                        $intermediaryInheritOrOverride->addSegment(new Segment("->"));
                    }
                    $intermediaryInheritOrOverride->addSegment(new Segment($this->reflectionMethod->getName()));
                    $intermediaryInheritOrOverride->addSegment(new Segment('</span>', true));
                    break;
                }
                $parentReflectionObject = $parentReflectionObject->getParentClass();
                $isFirst = false;
            }
        }

        $characterCount = null;
        $parameterIntermediaries = [];

        if ($this->reflectionMethod->getParameters()) {
            $characterCount = 0;
            foreach ($this->reflectionMethod->getParameters() as $parameter) {
                $parameterIntermediary = new Intermediary;
                $parameterIntermediary->merge((new MethodRenderer\ParameterRenderer(
                    $this->configuration,
                    $parameter
                ))->getIntermediary());
                $parameterIntermediaries[] = $parameterIntermediary;
                $characterCount += $parameterIntermediary->getStringLengthOfSegments(false);
            }
        }

        $intermediaryEnd = new Intermediary;
        $intermediaryEnd->addSegment(new Segment(')'));
        $intermediaryEnd->addSegment(new Segment($this->endingCharacter));

        if ($parameterIntermediaries && $characterCount) {
            $characterCount += $intermediary->getStringLengthOfSegments(false);
            $characterCount += (strlen(", ") * (count($parameterIntermediaries)-1));
            $intermediary->addSegment(new Segment('<span class="section--method-parameters">', true));
            if ($characterCount > HtmlFormatter::PSR_2_SOFT_CHARACTER_LIMIT) {
                $isFirst = true;
                foreach ($parameterIntermediaries as $parameterIntermediary) {
                    if (false == $isFirst) {
                        $intermediary->addSegment(new Segment(','));
                    }
                    $intermediary->addSegment(new Segment(PHP_EOL));
                    $this->indentIntermediary($intermediary, ($this->level+1));
                    $intermediary->merge($parameterIntermediary);
                    $isFirst = false;
                }
                $intermediary->addSegment(new Segment(PHP_EOL));
                $this->indentIntermediary($intermediary, $this->level);
            } else {
                $isFirst = true;
                foreach ($parameterIntermediaries as $parameterIntermediary) {
                    if (false == $isFirst) {
                        $intermediary->addSegment(new Segment(', '));
                    }
                    $intermediary->merge($parameterIntermediary);
                    $isFirst = false;
                }
            }
            $intermediary->addSegment(new Segment('</span>', true));
            $intermediary->merge($intermediaryEnd);
            $intermediary->merge($intermediaryInheritOrOverride);
        } else {
            $intermediary->merge($intermediaryEnd);
            $intermediary->merge($intermediaryInheritOrOverride);
        }

        $intermediary->addSegment(new Segment(PHP_EOL));

        return $intermediary;
    }

    public static function getMagicMethodNames()
    {
        return [
            "__construct",
            "__destruct",
            "__call",
            "__callStatic",
            "__get",
            "__set",
            "__isset",
            "__unset",
            "__sleep",
            "__wakeup",
            "__toString",
            "__invoke",
            "__set_state",
            "__clone",
            "__debugInfo",
        ];
    }

    /**
     * @param string $methodName
     */
    public static function isMethodNameMagic($methodName)
    {
        return (
            is_string($methodName)
            && in_array($methodName, self::getMagicMethodNames())
        );
    }
}
