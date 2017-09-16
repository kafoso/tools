<?php
namespace Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer;

use Kafoso\Tools\Debug\Dumper\HtmlFormatter;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;
use Kafoso\Tools\Exception\Formatter;

abstract class AbstractMultiLevelRenderer extends AbstractRenderer
{
    protected $level;
}
