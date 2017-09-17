<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type\ObjectRenderer;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Configuration;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\AbstractMultiLevelRenderer;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type;
use Kafoso\Tools\Exception\Formatter;

class PropertyRenderer extends AbstractMultiLevelRenderer
{
    private $owningReflectionObject;
    private $reflectionProperty;
    private $value;
    private $isRenderingOverrideComment;

    /**
     * @param null|string $endingCharacter
     * @param mixed $value
     * @param int $level
     * @param bool $isRenderingOverrideComment
     */
    public function __construct(
        Configuration $configuration,
        $endingCharacter,
        \ReflectionObject $owningReflectionObject,
        \ReflectionProperty $reflectionProperty,
        $value,
        $level,
        array $previousSplObjectHashes,
        $isRenderingOverrideComment = false
    )
    {
        $this->configuration = $configuration;
        $this->endingCharacter = $endingCharacter;
        $this->owningReflectionObject = $owningReflectionObject;
        $this->reflectionProperty = $reflectionProperty;
        $this->value = $value;
        $this->level = $level;
        $this->previousSplObjectHashes = $previousSplObjectHashes;
        $this->isRenderingOverrideComment = $isRenderingOverrideComment;
    }

    /**
     * @inheritDoc
     */
    public function getIntermediary()
    {
        $intermediary = new Intermediary;
        $this->indentIntermediary($intermediary, $this->level);
        $intermediary->addSegment(new Segment('<span class="syntax--storage syntax--modifier">', true));
        $space = " ";
        if ($this->reflectionProperty->isPrivate()) {
            $intermediary->addSegment(new Segment("private"));
        } elseif ($this->reflectionProperty->isProtected()) {
            $intermediary->addSegment(new Segment("protected"));
        } elseif ($this->reflectionProperty->isPublic()) {
            $intermediary->addSegment(new Segment("public"));
        } else {
            $space = "";
        }
        if ($this->reflectionProperty->isStatic()) {
            $intermediary->addSegment(new Segment("{$space}static"));
        }
        $intermediary->addSegment(new Segment('</span>', true));
        $intermediary->addSegment(new Segment(" "));
        $intermediary->addSegment(new Segment('<span class="syntax--variable">', true));
        $intermediary->addSegment(new Segment("\${$this->reflectionProperty->getName()}"));
        $intermediary->addSegment(new Segment('</span>', true));
        $intermediary->addSegment(new Segment(" = "));

        $subIntermediary = $this->generateIntermediaryBasedOnDataType($this->value, $this->level);

        $commentIntermediaries = [];
        if (is_string($this->value)) {
            $length = mb_strlen($this->value);
            $commentIntermediary = new Intermediary;
            $commentIntermediary->addSegment(new Segment("Length: {$length}"));
            $commentIntermediaries[] = $commentIntermediary;
        }
        if ($this->isRenderingOverrideComment) {
            $currentReflectionObject = $this->owningReflectionObject->getParentClass();
            $isFirst = true;
            while ($currentReflectionObject) {
                if (false == $isFirst && $currentReflectionObject->getName() == $this->owningReflectionObject->getName()) {
                    break;
                }
                if ($currentReflectionObject->hasProperty($this->reflectionProperty->getName())) {
                    $commentIntermediary = new Intermediary;
                    $commentIntermediary->addSegment(new Segment('<span class="syntax--keyword syntax--other syntax--phpdoc">', true));
                    $commentIntermediary->addSegment(new Segment("@override"));
                    $commentIntermediary->addSegment(new Segment('</span>', true));
                    $commentIntermediary->addSegment(new Segment(" "));
                    $commentIntermediary->addSegment(new Segment('\\' . $currentReflectionObject->getName()));
                    $commentIntermediary->addSegment(new Segment("->"));
                    $commentIntermediary->addSegment(new Segment('$' . $this->reflectionProperty->getName()));
                    $commentIntermediaries[] = $commentIntermediary;
                    break;
                }
                $isFirst = false;
                $currentReflectionObject = $currentReflectionObject->getParentClass();
            }
        }
        if ($commentIntermediaries) {
            $commentIntermediary = new Intermediary;
            $commentIntermediary->addSegment(new Segment(' '));
            $commentIntermediary->addSegment(new Segment('<span class="syntax--comment syntax--line syntax--double-slash">', true));
            $commentIntermediary->addSegment(new Segment("// "));
            $isFirst = true;
            foreach ($commentIntermediaries as $ci) {
                if (false == $isFirst) {
                    $commentIntermediary->addSegment(new Segment(' // '));
                }
                $commentIntermediary->merge($ci);
                $isFirst = false;
            }
            $commentIntermediary->addSegment(new Segment('</span>', true));
            $subIntermediary->merge($commentIntermediary);
        }


        $intermediary->merge($subIntermediary);

        return $intermediary;
    }
}
