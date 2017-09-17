<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;

class Intermediary
{
    private $segments = [];

    /**
     * @return $this
     */
    public function addSegment(Segment $segment)
    {
        $this->segments[] = $segment;
        return $this;
    }

    /**
     * @return array
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * @param bool $isRenderingHtml
     */
    public function getStringLengthOfSegments($isRenderingHtml = false)
    {
        return mb_strlen($this->render($isRenderingHtml));
    }

    /**
     * @return $this
     */
    public function merge(Intermediary $intermediary)
    {
        if ($intermediary === $this) {
            throw new \UnexpectedValueException(sprintf(
                "Cannot merge \\%s with itself",
                __CLASS__
            ));
        }
        foreach ($intermediary->getSegments() as $segment) {
            $this->addSegment($segment);
        }
        return $this;
    }

    /**
     * @param bool $isRenderingHtml
     * @return string
     */
    public function render($isRenderingHtml = false)
    {
        $html = "";
        foreach ($this->segments as $segment) {
            $html .= $segment->render($isRenderingHtml);
        }
        return $html;
    }

    /**
     * @return bool
     */
    public function hasSegments()
    {
        return count($this->getSegments()) > 0;
    }
}
