<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;
use Kafoso\Tools\Exception\Formatter;

abstract class AbstractRenderer
{
    protected $configuration;
    protected $endingCharacter = null;
    protected $previousSplObjectHashes = [];

    /**
     * @param mixed $value
     * @param null|int $level
     * @return AbstractRenderer
     */
    public function generateRendererBasedOnDataType($value, $level = null, $endingCharacter = ";")
    {
        if (is_null($value)) {
            return new Type\NullRenderer($endingCharacter);
        } elseif (is_scalar($value)) {
            return new Type\ScalarRenderer($this->configuration, $endingCharacter, $value);
        } elseif (is_array($value)) {
            if (count($value) > 0 && is_int($level) && $level >= $this->configuration->getDepth()) {
                return new Type\ArrayRenderer\OmittedRenderer(
                    $this->configuration,
                    ",",
                    $value,
                    $level,
                    ($this->previousSplObjectHashes ?: [])
                );
            }
            return new Type\ArrayRenderer(
                $this->configuration,
                $endingCharacter,
                $value,
                $level,
                ($this->previousSplObjectHashes ?: [])
            );
        } elseif (is_object($value)) {
            $hash = spl_object_hash($value);
            if ($this->previousSplObjectHashes && in_array($hash, $this->previousSplObjectHashes)) {
                return new Type\ObjectRenderer\RecursionRenderer(
                    $this->configuration,
                    $endingCharacter,
                    $value,
                    $level,
                    []
                );
            } elseif ($this->configuration->getSuppressedClassNames()
                && in_array(get_class($value), $this->configuration->getSuppressedClassNames())) {
                $previousSplObjectHashes[] = $hash;
                return new Type\ObjectRenderer\SuppressedRenderer(
                    $this->configuration,
                    $endingCharacter,
                    $value,
                    $level,
                    $previousSplObjectHashes
                );
            } elseif (is_int($level) && $level >= $this->configuration->getDepth()) {
                $previousSplObjectHashes[] = $hash;
                return new Type\ObjectRenderer\OmittedRenderer(
                    $this->configuration,
                    $endingCharacter,
                    $value,
                    $level,
                    $previousSplObjectHashes
                );
            }
            return new Type\ObjectRenderer(
                $this->configuration,
                $endingCharacter,
                $value,
                $level,
                ($this->previousSplObjectHashes ?: [])
            );
        } elseif (is_resource($value)) {
            return new Type\ResourceRenderer($this->configuration, $endingCharacter, $value);
        }
        return new Type\UnknownRenderer($endingCharacter);
    }

    /**
     * @param mixed $value
     * @param null|int $level
     * @return Intermediary
     */
    public function generateIntermediaryBasedOnDataType($value, $level = null, $endingCharacter = ";")
    {
        return $this->generateRendererBasedOnDataType($value, $level, $endingCharacter)->getIntermediary();
    }

    /**
     * @param int $level
     */
    public function indentIntermediary(Intermediary $intermediary, $level)
    {
        if ($level > 0) {
            for ($i=$level;$i>0;$i--) {
                $intermediary->addSegment(new Segment('<span class="invisible-character leading-whitespace indent-guide">', true));
                $intermediary->addSegment(new Segment(HtmlFormatter::PSR_2_INDENTATION_CHARACTERS));
                $intermediary->addSegment(new Segment('</span>', true));
            }
        }
    }

    /**
     * @return \Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary
     */
    abstract public function getIntermediary();
}
