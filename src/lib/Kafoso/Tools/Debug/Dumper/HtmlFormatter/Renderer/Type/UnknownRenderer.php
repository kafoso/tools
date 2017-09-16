<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Configuration;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\AbstractRenderer;
use Kafoso\Tools\Exception\Formatter;

class UnknownRenderer extends AbstractRenderer
{
    public function __construct($endingCharacter)
    {
        $this->endingCharacter = $endingCharacter;
    }

    public function getIntermediary()
    {
        $intermediary = new Intermediary;
        $intermediary->addSegment(new Segment('<span class="syntax--language syntax--unknown">', true));
        $intermediary->addSegment(new Segment("(Unknown)"));
        $intermediary->addSegment(new Segment('</span>', true));
        $intermediary->addSegment(new Segment($this->endingCharacter));
        return $intermediary;
    }
}
