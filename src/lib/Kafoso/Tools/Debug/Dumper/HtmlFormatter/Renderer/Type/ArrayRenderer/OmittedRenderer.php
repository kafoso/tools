<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type\ArrayRenderer;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type\ArrayRenderer;

class OmittedRenderer extends ArrayRenderer
{
    /**
     * @inheritDoc
     */
    public function getIntermediary()
    {
        $intermediary = new Intermediary;
        $intermediary->addSegment(new Segment("[ ... ]"));
        if (is_array($this->parentValue)) {
            $intermediary->addSegment(new Segment(","));
        } else {
            $intermediary->addSegment(new Segment(";"));
        }
        $intermediary->addSegment(new Segment(" "));
        $intermediary->addSegment(new Segment('<span class="syntax--comment syntax--line syntax--double-slash">', true));
        $intermediary->addSegment(new Segment("// Size: " . count($this->array)));
        $intermediary->addSegment(new Segment(" / Omitted; collapse level reached"));
        $intermediary->addSegment(new Segment('</span>', true));
        return $intermediary;
    }
}
