<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Configuration;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\AbstractRenderer;
use Kafoso\Tools\Exception\Formatter;

class ResourceRenderer extends AbstractRenderer
{
    private $resource;

    /**
     * @param null|string $endingCharacter
     * @param resource $resource
     */
    public function __construct(Configuration $configuration, $endingCharacter, $resource)
    {
        $this->configuration = $configuration;
        $this->endingCharacter = $endingCharacter;
        $this->resource = $resource;
    }

    /**
     * @inheritDoc
     */
    public function getIntermediary()
    {
        $intermediary = new Intermediary;
        $intermediary->addSegment(new Segment("Resource #" . intval($this->resource)));
        $intermediary->addSegment(new Segment("; "));
        $intermediary->addSegment(new Segment('<span class="syntax--comment syntax--line syntax--double-slash">', true));
        $intermediary->addSegment(new Segment("// Type: " . get_resource_type($this->resource)));
        $intermediary->addSegment(new Segment('</span>', true));
        return $intermediary;
    }
}
