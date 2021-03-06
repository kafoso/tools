<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type\ObjectRenderer;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type\ObjectRenderer;

class OmittedRenderer extends ObjectRenderer
{
    /**
     * @inheritDoc
     */
    public function getIntermediary()
    {
        $intermediary = $this->getIntermediaryWithClassDeclaration();
        $intermediary->addSegment(new Segment('; '));
        $intermediary->addSegment(new Segment('<span class="syntax--comment syntax--line syntax--double-slash">', true));
        $intermediary->addSegment(new Segment(sprintf(
            " // Omitted; depth level of %d reached",
            $this->configuration->getDepth()
        )));
        $intermediary->addSegment(new Segment('</span>', true));
        return $intermediary;
    }
}
