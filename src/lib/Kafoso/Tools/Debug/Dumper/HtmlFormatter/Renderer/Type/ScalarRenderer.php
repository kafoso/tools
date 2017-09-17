<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Configuration;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\AbstractRenderer;
use Kafoso\Tools\Exception\Formatter;
use Kafoso\Tools\Generic\HTML;

class ScalarRenderer extends AbstractRenderer
{
    const REGEX_ASCII_CHARACTERS = '[\x00-\x1F\x7F]';

    private $value;

    /**
     * @param null|string $endingCharacter
     * @param bool|double|float|int|string
     */
    public function __construct(Configuration $configuration, $endingCharacter, $value)
    {
        $this->configuration = $configuration;
        $this->endingCharacter = $endingCharacter;
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function getIntermediary()
    {
        $value = null;
        $intermediary = new Intermediary;
        if (is_bool($this->value) || is_float($this->value) || is_int($this->value)) {
            if (is_bool($this->value)) {
                $intermediary->addSegment(new Segment('<span class="syntax--language syntax--constant">', true));
                $intermediary->addSegment(new Segment($this->value ? "true" : "false"));
                $intermediary->addSegment(new Segment('</span>', true));
            } elseif (is_float($this->value) || is_int($this->value)) {
                $value = $this->value;
                if ($value < 0) {
                    $intermediary->addSegment(new Segment("-"));
                    $value = abs($value);
                }
                $intermediary->addSegment(new Segment('<span class="syntax--language syntax--constant syntax--numeric">', true));
                $intermediary->addSegment(new Segment(strval($value)));
                $intermediary->addSegment(new Segment('</span>', true));
            }
        } else {
            $value = strval($this->value);
            $lengthOriginal = mb_strlen($value);
            $lengthModified = $lengthOriginal;
            $regex = sprintf(
                "/(\\$|\\\"|\\\\|%s)/",
                self::REGEX_ASCII_CHARACTERS
            );
            $split = preg_split($regex, $value, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            if (count($split) > 1) {
                $intermediary->addSegment(new Segment('<span class="syntax--language syntax--string">', true));
                $intermediary->addSegment(new Segment('"'));
                $subIntermediary = new Intermediary;
                $i = 0;
                foreach ($split as $part) {
                    if (0 == $i%2) {
                        $subIntermediary->addSegment(new Segment($part));
                    } else {
                        $subCssClasses = "syntax--constant syntax--character syntax--escape";
                        if ("\e" === $part) {
                            $part = "\\e";
                        } elseif ("\n" === $part) {
                            $part = "\\n";
                        } elseif ("\r" === $part) {
                            $part = "\\r";
                        } elseif ("\t" === $part) {
                            $part = "\\t";
                        } else {
                            preg_match("/" . self::REGEX_ASCII_CHARACTERS . "/", $part, $match);
                            if ($match) {
                                $part = "\\x" . bin2hex($match[0]);
                                $subCssClasses = "syntax--constant syntax--numeric syntax--octal";
                            } else {
                                $part = "\\" . $part;
                            }
                        }
                        $subIntermediary->addSegment(new Segment(
                            sprintf('<span class="%s">', HTML::encode($subCssClasses)),
                            true
                        ));
                        $subIntermediary->addSegment(new Segment($part));
                        $subIntermediary->addSegment(new Segment('</span>', true));
                    }
                    $i++;
                }
                $lengthModified = mb_strlen($subIntermediary->render(false));
                $intermediary->merge($subIntermediary);
                $intermediary->addSegment(new Segment('"'));
                $intermediary->addSegment(new Segment('</span>', true));
            } else {
                $intermediary->addSegment(new Segment('<span class="syntax--language syntax--string">', true));
                $intermediary->addSegment(new Segment('"' . $value . '"'));
                $intermediary->addSegment(new Segment('</span>', true));
            }
        }
        if ($this->endingCharacter) {
            $intermediary->addSegment(new Segment($this->endingCharacter));
        }
        return $intermediary;
    }
}
