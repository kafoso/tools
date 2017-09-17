<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;

use Kafoso\Tools\Generic\HTML;

class Segment
{
    private $string;
    private $isHtml;

    /**
     * @param string $string
     * @param bool $isHtml
     */
    public function __construct($string, $isHtml = false)
    {
        $this->string = $string;
        $this->isHtml = $isHtml;
    }

    /**
     * @param bool $isRenderingHtml
     */
    public function render($isRenderingHtml = false)
    {
        if ($this->isHtml && false == $isRenderingHtml) {
            return "";
        } elseif (false == $this->isHtml && $isRenderingHtml) {
            return HTML::encode($this->string);
        }
        return $this->string;
    }

    /**
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * @return bool
     */
    public function isHtml()
    {
        return $this->isHtml;
    }
}
