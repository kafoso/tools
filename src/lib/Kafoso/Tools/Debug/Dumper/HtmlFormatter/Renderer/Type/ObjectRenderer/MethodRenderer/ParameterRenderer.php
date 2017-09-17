<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type\ObjectRenderer\MethodRenderer;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Configuration;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\AbstractRenderer;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type\ArrayRenderer;
use Kafoso\Tools\Exception\Formatter;

class ParameterRenderer extends AbstractRenderer
{
    private $reflectionParameter;

    public function __construct(
        Configuration $configuration,
        \ReflectionParameter $reflectionParameter
    )
    {
        $this->configuration = $configuration;
        $this->reflectionParameter = $reflectionParameter;
    }

    /**
     * @inheritDoc
     */
    public function getIntermediary()
    {
        $intermediary = new Intermediary;

        $intermediary->addSegment(new Segment('<span class="section--method-parameter-type-hints">', true));
        if ($this->reflectionParameter->isPassedByReference()) {
            $intermediary->addSegment(new Segment('<span class="syntax--storage syntax--modifier syntax--reference">', true));
            $intermediary->addSegment(new Segment('&'));
            $intermediary->addSegment(new Segment('</span>', true));
        } elseif ($this->reflectionParameter->allowsNull()) {
            $intermediary->addSegment(new Segment('?'));
        }
        if ($this->reflectionParameter->isCallable() || $this->reflectionParameter->isArray()) {
            $intermediary->addSegment(new Segment('<span class="syntax--support syntax--storage syntax--type">', true));
            if ($this->reflectionParameter->isCallable()) {
                $intermediary->addSegment(new Segment("callable"));
            } else {
                $intermediary->addSegment(new Segment("array"));
            }
            $intermediary->addSegment(new Segment('</span>', true));
            $intermediary->addSegment(new Segment(' '));
        } elseif ($this->reflectionParameter->hasType()) {
            $typeName = (string)$this->reflectionParameter->getType();
            if ("array" == strtolower($typeName)) {
                $intermediary->addSegment(new Segment('<span class="syntax--support syntax--class">', true));
                $intermediary->addSegment(new Segment("array"));
                $intermediary->addSegment(new Segment('</span>', true));
            } else {
                if ($this->reflectionParameter->getType()->isBuiltin()) {
                    $intermediary->addSegment(new Segment('<span class="syntax--support syntax--class syntax--builtin">', true));
                } else {
                    $intermediary->addSegment(new Segment('<span class="syntax--support syntax--class">', true));
                    $intermediary->addSegment(new Segment('\\'));
                }
                $intermediary->addSegment(new Segment($typeName));
                $intermediary->addSegment(new Segment('</span>', true));
            }
            $intermediary->addSegment(new Segment(" "));
        }
        $intermediary->addSegment(new Segment('</span>', true));
        $intermediary->addSegment(new Segment('<span class="syntax--variable">', true));
        $intermediary->addSegment(new Segment('$' . $this->reflectionParameter->getName()));
        $intermediary->addSegment(new Segment('</span>', true));
        if ($this->reflectionParameter->isDefaultValueAvailable()) {
            $intermediary->addSegment(new Segment(' '));
            $intermediary->addSegment(new Segment('<span class="syntax--keyword syntax--operator syntax--assignment">', true));
            $intermediary->addSegment(new Segment('='));
            $intermediary->addSegment(new Segment('</span>', true));
            $intermediary->addSegment(new Segment(' '));
            @list($class, $constant) = @explode('::', $this->reflectionParameter->getDefaultValueConstantName());
            if ($class && $constant) {
                $intermediary->addSegment(new Segment('<span class="syntax--support syntax--other syntax--namespace">', true));
                $intermediary->addSegment(new Segment('<span class="syntax--punctuation syntax--separator syntax--inheritance">', true));
                $intermediary->addSegment(new Segment('\\'));
                $intermediary->addSegment(new Segment('</span>', true));
                $intermediary->addSegment(new Segment('</span>', true));
                $intermediary->addSegment(new Segment('<span class="syntax--support syntax--class">', true));
                $intermediary->addSegment(new Segment($class));
                $intermediary->addSegment(new Segment('</span>', true));
                $intermediary->addSegment(new Segment('<span class="syntax--keyword syntax--operator syntax--class">', true));
                $intermediary->addSegment(new Segment('::'));
                $intermediary->addSegment(new Segment('</span>', true));
                $intermediary->addSegment(new Segment('<span class="syntax--constant syntax--other syntax--class">', true));
                $intermediary->addSegment(new Segment($constant));
                $intermediary->addSegment(new Segment('</span>', true));
            } else {
                $renderer = $this->generateRendererBasedOnDataType(
                    $this->reflectionParameter->getDefaultValue(),
                    null,
                    null
                );
                if ($renderer instanceof ArrayRenderer) {
                    $renderer
                        ->setIsMultiline(false)
                        ->setIsShowingSizeComment(false);
                }
                $intermediary->merge($renderer->getIntermediary());
            }
        }

        return $intermediary;
    }
}
