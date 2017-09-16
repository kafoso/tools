<?php
namespace Kafoso\Tools\Debug\Dumper;

use Kafoso\Tools\Debug\Dumper;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Intermediary\Segment;
use Kafoso\Tools\Debug\Dumper\HtmlFormatter\Renderer;
use Kafoso\Tools\Generic\HTML;
use Kafoso\Tools\Generic\HTML\ViewRenderer;

class HtmlFormatter extends AbstractFormatter
{
    const LEVEL_MAX = 10;
    const COLLAPSELEVEL_MAX = 40;
    const COOKIE_NAME = "Kafoso_Tools_Debug_Dumper";

    protected $configuration; // Move to AbstractFormatter

    public function __construct($var, $depth = null)
    {
        parent::__construct($var, $depth);
        $this->configuration = HtmlFormatter\Configuration::createFromSuperglobalCookie(); // Add to constructor
    }

    public function render()
    {
        $origin = $this->getOrigin();
        $viewRenderer = new ViewRenderer("Kafoso/Tools/Debug/Dumper/HtmlFormatter/render.phtml", [
            'configuration' => $this->configuration,
            'PSR_2_SOFT_CHARACTER_LIMIT' => static::PSR_2_SOFT_CHARACTER_LIMIT,
            'css' => $this->getCss(),
            'innerHtml' => $this->renderInner(),
            'javascript' => $this->getJavascript(),
            'origin' => $origin,
            'truncatedGenericClasses' => $this->configuration->getTruncatedGenericClasses(),
            'uuid' => $this->getUuid(),
        ]);
        $viewRenderer->setBaseDirectory(realpath(__DIR__ . str_repeat("/..", 5)) . "/view");
        return $viewRenderer->render();
    }

    public function renderInner()
    {
        $intermediary = null;
        switch (strtolower(gettype($this->var))) {
            case "array":
                $intermediary = (new Renderer\Type\ArrayRenderer(
                    $this->configuration,
                    ";",
                    $this->var,
                    null,
                    0,
                    []
                ))->getIntermediary();
                break;
            case "boolean":
            case "double":
            case "float":
            case "integer":
            case "string":
                $intermediary = (new Renderer\Type\ScalarRenderer(
                    $this->configuration,
                    ";",
                    $this->var
                ))->getIntermediary();
                break;
                break;
            case "object":
                $previousSplObjectHashes = [spl_object_hash($this->var)];
                $intermediary = (new Renderer\Type\ObjectRenderer(
                    $this->configuration,
                    ";",
                    $this->var,
                    0,
                    $previousSplObjectHashes
                ))->getIntermediary();
                break;
            case "null":
                $previousSplObjectHashes = [spl_object_hash($this->var)];
                $intermediary = (new Renderer\Type\NullRenderer(
                    ";"
                ))->getIntermediary();
                break;
            case "resource":
                $intermediary = (new Renderer\Type\ResourceRenderer(
                    $this->configuration,
                    ";",
                    $this->var
                ))->getIntermediary();
                break;
        }
        if ($intermediary) {
            return $intermediary->render(true);
        }
        return "";
    }

    public function getCss()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat("/..", 6));
        $css = file_get_contents($baseDirectory . "/resources/dest/Kafoso/Tools/Debug/Dumper/HtmlFormatter/theme/css/dark-one-ui.css");
        return $css;
    }

    public function getJavascript()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat("/..", 6));
        $js = file_get_contents($baseDirectory . "/resources/dest/Kafoso/Tools/Debug/Dumper/HtmlFormatter/js/main.js");
        $js = sprintf(
            '(function(uuid, cookieName){%s})("%s","%s")',
            $js,
            $this->getUuid(),
            self::COOKIE_NAME
        );
        return $js;
    }

    /**
     * Looks back through the debug_backtrace to determine from where the output originated.
     * @return ?array
     */
    public function getOrigin()
    {
        $calledFrom = null;
        $rootDirectory = realpath(__DIR__ . str_repeat("/..", 5));
        foreach (debug_backtrace() as $v) {
            if (0 === stripos($v['file'], $rootDirectory)) {
                continue;
            }
            $calledFrom = $v;
            break;
        }
        return $calledFrom;
    }
}
