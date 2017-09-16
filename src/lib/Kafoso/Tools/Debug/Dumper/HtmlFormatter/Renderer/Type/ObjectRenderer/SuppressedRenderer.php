<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type\ObjectRenderer;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type\ObjectRenderer;

class SuppressedRenderer extends ObjectRenderer
{
    public function getIntermediary()
    {
        $intermediary = $this->getIntermediaryWithClassDeclaration();
        $intermediary->addSegment(new Segment('; '));
        $intermediary->addSegment(new Segment('<span class="syntax--comment syntax--line syntax--double-slash">', true));
        $intermediary->addSegment(new Segment('// Suppressed; you have chosen to hide this object'));
        $intermediary->addSegment(new Segment('</span>', true));
        return $intermediary;
    }
}
