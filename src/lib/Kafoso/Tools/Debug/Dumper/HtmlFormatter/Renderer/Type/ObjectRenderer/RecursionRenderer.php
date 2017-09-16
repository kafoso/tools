<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type\ObjectRenderer;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type\ObjectRenderer;
use Kafoso\Tools\Generic\HTML;

class RecursionRenderer extends ObjectRenderer
{
    public function getIntermediary()
    {
        $intermediary = $this->getIntermediaryWithClassDeclaration();
        $intermediary->addSegment(new Segment('; '));
        $intermediary->addSegment(new Segment('<span class="syntax--comment syntax--line syntax--double-slash">', true));
        $intermediary->addSegment(new Segment('// Omitted; recursion'));
        $intermediary->addSegment(new Segment('</span>', true));
        return $intermediary;
    }
}
