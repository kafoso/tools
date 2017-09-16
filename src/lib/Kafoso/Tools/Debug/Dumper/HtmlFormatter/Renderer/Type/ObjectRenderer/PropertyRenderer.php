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
    private $reflectionProperty;
    private $value;

    /**
     * @param mixed $value
     * @param int $level
     */
    public function __construct(
        Configuration $configuration,
        $endingCharacter,
        \ReflectionProperty $reflectionProperty,
        $value,
        $level,
        array $previousSplObjectHashes
    )
    {
        $this->configuration = $configuration;
        $this->endingCharacter = $endingCharacter;
        $this->reflectionProperty = $reflectionProperty;
        $this->value = $value;
        $this->level = $level;
        $this->previousSplObjectHashes = $previousSplObjectHashes;
    }

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

        $subIntermediary = $this->generateIntermediaryBasedOnDataType($this->value, ($this->level-1));
        if (is_string($this->value)) {
            $length = mb_strlen($this->value);
            $subIntermediary->addSegment(new Segment(" "));
            $subIntermediary->addSegment(new Segment('<span class="syntax--comment syntax--line syntax--double-slash">', true));
            $subIntermediary->addSegment(new Segment("// Length: {$length}"));
            $subIntermediary->addSegment(new Segment('</span>', true));
        }
        $intermediary->merge($subIntermediary);

        return $intermediary;
    }
}
