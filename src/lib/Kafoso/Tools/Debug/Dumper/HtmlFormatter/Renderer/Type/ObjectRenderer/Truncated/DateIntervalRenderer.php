<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type\ObjectRenderer\Truncated;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type\ObjectRenderer;

class DateIntervalRenderer extends ObjectRenderer
{
    public function getIntermediary()
    {
        $str = self::objectToString($this->object);

        $intermediary = new Intermediary;
        $intermediary->addSegment(new Segment($this->endingCharacter));
        $intermediary->addSegment(new Segment(" "));
        $intermediary->addSegment(new Segment('<span class="syntax--comment syntax--line syntax--double-slash">', true));
        $intermediary->addSegment(new Segment('// Truncated: ' . $str));
        $intermediary->addSegment(new Segment('</span>', true));
        return $intermediary;
    }

    public static function objectToString(\DateInterval $dateInterval)
    {
        $segments = [];
        $map = [
            "y" => [
                "singular" => "year",
                "plural" => "years",
            ],
            "m" => [
                "singular" => "month",
                "plural" => "months",
            ],
            "d" => [
                "singular" => "day",
                "plural" => "days",
            ],
            "h" => [
                "singular" => "hour",
                "plural" => "hours",
            ],
            "i" => [
                "singular" => "minute",
                "plural" => "minutes",
            ],
            "s" => [
                "singular" => "second",
                "plural" => "seconds",
            ],
        ];
        foreach ($map as $property => $names) {
            $int = $dateInterval->$property;
            if (0 !== $int) {
                $segments[] = $dateInterval->$property
                    . " "
                    . (1 === $int ? $names['singular'] : $names['plural']);
            }
        }
        $str = implode(", ", $segments);
        if ($str) {
            if ($dateInterval->invert) {
                $str = "Minus " . $str;
            }
        } else {
            $str = "(No difference)";
        }
        return $str;
    }
}
