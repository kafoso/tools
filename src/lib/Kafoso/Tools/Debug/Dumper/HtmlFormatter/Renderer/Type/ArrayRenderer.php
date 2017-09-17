<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Configuration;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\AbstractMultiLevelRenderer;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer\Type;
use Kafoso\Tools\Exception\Formatter;

class ArrayRenderer extends AbstractMultiLevelRenderer
{
    protected $array;
    protected $parentValue;
    protected $isMultiline = true;
    protected $isShowingSizeComment = true;

    /**
     * @param null|string $endingCharacter
     * @param mixed $parentValue
     * @param int $level
     * @param array $previousSplObjectHashes
     */
    public function __construct(
        Configuration $configuration,
        $endingCharacter,
        array $array,
        $parentValue,
        $level,
        array $previousSplObjectHashes,
        $isMultiline = true,
        $isShowingSizeComment = true
    )
    {
        $this->configuration = $configuration;
        $this->endingCharacter = $endingCharacter;
        $this->array = $array;
        $this->parentValue = $parentValue;
        $this->level = $level;
        $this->previousSplObjectHashes = $previousSplObjectHashes;
        $this->isMultiline = $isMultiline;
        $this->isShowingSizeComment = $isShowingSizeComment;
    }

    /**
     * @param bool $isMultiline
     * @return $this
     */
    public function setIsMultiline($isMultiline)
    {
        $this->isMultiline = $isMultiline;
        return $this;
    }

    /**
     * @param bool $isShowingSizeComment
     * @return $this
     */
    public function setIsShowingSizeComment($isShowingSizeComment)
    {
        $this->isShowingSizeComment = $isShowingSizeComment;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIntermediary()
    {
        $intermediary = new Intermediary;
        if (count($this->array) > 0) {
            $intermediary->addSegment(new Segment("["));
            if ($this->isShowingSizeComment) {
                $intermediary->addSegment(new Segment(" "));
                $intermediary->addSegment(new Segment('<span class="syntax--comment syntax--line syntax--double-slash">', true));
                $intermediary->addSegment(new Segment("// Size: " . count($this->array)));
                $intermediary->addSegment(new Segment('</span>', true));
            }
            if ($this->isMultiline) {
                $intermediary->addSegment(new Segment(PHP_EOL));
            }
            $isFirst = true;
            foreach ($this->array as $k => $v) {
                if ($this->isMultiline) {
                    static::indentIntermediary($intermediary, ($this->level+1));
                } elseif (false == $isFirst) {
                    $intermediary->addSegment(new Segment(", "));
                }
                $intermediary->merge((new ScalarRenderer($this->configuration, null, $k))->getIntermediary());
                $intermediary->addSegment(new Segment(" => "));

                $subIntermediary = $this->generateIntermediaryBasedOnDataType($v, $this->level, "");
                $intermediary->merge($subIntermediary);
                if ($this->isMultiline) {
                    $intermediary->addSegment(new Segment(","));
                    $intermediary->addSegment(new Segment(PHP_EOL));
                }
                $isFirst = false;
            }
            if ($this->isMultiline) {
                static::indentIntermediary($intermediary, $this->level);
            }
            $intermediary->addSegment(new Segment("]" . $this->endingCharacter));
        } else {
            $intermediary->addSegment(new Segment("[]" . $this->endingCharacter));
            if ($this->isShowingSizeComment) {
                $intermediary->addSegment(new Segment(" "));
                $intermediary->addSegment(new Segment('<span class="syntax--comment syntax--line syntax--double-slash">', true));
                $intermediary->addSegment(new Segment("// Size: 0"));
                $intermediary->addSegment(new Segment('</span>', true));
            }
        }
        return $intermediary;
    }
}
