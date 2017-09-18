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
    const COOKIE_NAME = "Kafoso_Tools_Debug_Dumper_HtmlFormatter";

    /**
     * @return string
     */
    public function render($var, $depth = null)
    {
        if (is_int($depth)) {
            $this->configuration->setDepth($depth);
        }
        $viewRenderer = new ViewRenderer("Kafoso/Tools/Debug/Dumper/HtmlFormatter/render.phtml", [
            'configuration' => $this->configuration,
            'PSR_2_SOFT_CHARACTER_LIMIT' => static::PSR_2_SOFT_CHARACTER_LIMIT,
            'css' => $this->getCss(),
            'innerHtml' => $this->renderInner($var),
            'javascript' => $this->getJavascript(),
            'origin' => $this->getOrigin(),
            'truncatedGenericClasses' => HtmlFormatter\Configuration::getTruncatedGenericClasses(),
            'uuid' => $this->getUuid(),
        ]);
        $viewRenderer->setBaseDirectory(realpath(__DIR__ . str_repeat("/..", 5)) . "/view");
        return $viewRenderer->render();
    }

    /**
     * @return null|string
     */
    public function renderInner($var, $depth = null)
    {
        if (is_int($depth)) {
            $this->configuration->setDepth($depth);
        }
        $intermediary = null;
        switch (strtolower(gettype($var))) {
            case "array":
                $intermediary = (new Renderer\Type\ArrayRenderer(
                    $this->configuration,
                    ";",
                    $var,
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
                    $var
                ))->getIntermediary();
                break;
                break;
            case "object":
                $previousSplObjectHashes = [spl_object_hash($var)];
                $intermediary = (new Renderer\Type\ObjectRenderer(
                    $this->configuration,
                    ";",
                    $var,
                    0,
                    $previousSplObjectHashes
                ))->getIntermediary();
                break;
            case "null":
                $intermediary = (new Renderer\Type\NullRenderer(
                    ";"
                ))->getIntermediary();
                break;
            case "resource":
                $intermediary = (new Renderer\Type\ResourceRenderer(
                    $this->configuration,
                    ";",
                    $var
                ))->getIntermediary();
                break;
        }
        if ($intermediary) {
            return $intermediary->render(true);
        }
        return "";
    }

    /**
     * @return string
     */
    public function getCss()
    {
        $baseDirectory = realpath(__DIR__ . str_repeat("/..", 6));
        $css = file_get_contents($baseDirectory . "/resources/dest/Kafoso/Tools/Debug/Dumper/HtmlFormatter/theme/css/dark-one-ui.css");
        return $css;
    }

    /**
     * @return string
     */
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
}
