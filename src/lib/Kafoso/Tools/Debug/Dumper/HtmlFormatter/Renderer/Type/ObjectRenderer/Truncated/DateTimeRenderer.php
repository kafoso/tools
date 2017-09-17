<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type\ObjectRenderer\Truncated;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type\ObjectRenderer;

class DateTimeRenderer extends ObjectRenderer
{
    /**
     * @inheritDoc
     */
    public function getIntermediary()
    {
        $intermediary = new Intermediary;
        $intermediary->addSegment(new Segment($this->endingCharacter));
        $intermediary->addSegment(new Segment(" "));
        $intermediary->addSegment(new Segment('<span class="syntax--comment syntax--line syntax--double-slash">', true));
        $intermediary->addSegment(new Segment('// Truncated: ' . $this->object->format("c")));
        $intermediary->addSegment(new Segment('</span>', true));
        return $intermediary;
    }
}
