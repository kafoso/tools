<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type\ObjectRenderer\Truncated;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type\ObjectRenderer;

class DatePeriodRenderer extends ObjectRenderer
{
    public function getIntermediary()
    {
        $str = "Start: " . $this->object->getStartDate()->format("c");
        $str .= ", End: " . $this->object->getEndDate()->format("c");
        $str .= ", Period: " . DateIntervalRenderer::objectToString($this->object->getDateInterval());

        $intermediary = new Intermediary;
        $intermediary->addSegment(new Segment($this->endingCharacter));
        $intermediary->addSegment(new Segment(" "));
        $intermediary->addSegment(new Segment('<span class="syntax--comment syntax--line syntax--double-slash">', true));
        $intermediary->addSegment(new Segment('// Truncated: ' . $str));
        $intermediary->addSegment(new Segment('</span>', true));
        return $intermediary;
    }
}
