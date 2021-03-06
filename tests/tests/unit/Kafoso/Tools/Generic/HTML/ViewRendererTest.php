<?php
namespace Kafoso\Tools\Tests\Unit\Generic\HTML;

use Kafoso\Tools\Generic\HTML\ViewRenderer;
use Kafoso\Tools\Traits\File\EOLNormalizer;

class ViewRendererTest extends \PHPUnit_Framework_TestCase
{
    use EOLNormalizer;

    public function testCanConstructWithoutPath()
    {
        $viewRenderer = new ViewRenderer;
        $this->assertInstanceOf(ViewRenderer::class, $viewRenderer);
        $this->assertNull($viewRenderer->getTemplatePath());
        $this->assertNull($viewRenderer->getBaseDirectory());
    }

    public function testCanConstructWithPath()
    {
        $viewRenderer = new ViewRenderer("foo");
        $this->assertSame("foo", $viewRenderer->getTemplatePath());
    }

    public function testSetAndGetVariables()
    {
        $viewRenderer = new ViewRenderer;
        $viewRenderer->foo = "bar";
        $this->assertSame("bar", $viewRenderer->foo);
    }

    /**
     * @dataProvider dataProvider_testConstructorThrowsExceptionWhenInvalidAdditionalVarsIsProvided
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Parameter "$additionalVars" is invalid. Expected one of: [null,array,object]. Found:
     */
    public function testConstructorThrowsExceptionWhenInvalidAdditionalVarsIsProvided($additionalVars)
    {
        new ViewRenderer("foo", $additionalVars);
    }

    public function dataProvider_testConstructorThrowsExceptionWhenInvalidAdditionalVarsIsProvided()
    {
        return array(
            array(1),
            array(false),
            array(M_PI),
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid parameter "$additionalVars". Expected object to be instance of \Kafoso\Tools\Generic\HTML\ViewRenderer. Found: \stdClass
     */
    public function testConstructorThrowsExceptionWhenNonViewRenderClassIsPassOnAsAdditionalVars()
    {
        new ViewRenderer("foo", new \stdClass);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Base directory does not exist on path:
     */
    public function testRenderThrowsExceptionWhenBaseDirectoryDoesNotExist()
    {
        $viewRenderer = new ViewRenderer;
        $viewRenderer->render();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessageRegExp /^Base directory is invalid; not a directory: (.+?)ViewRendererTest\.php$/
     */
    public function testRenderThrowsExceptionWhenBaseDirectoryIsNotADirectory()
    {
        $viewRenderer = new ViewRenderer;
        $viewRenderer->setBaseDirectory(__FILE__);
        $viewRenderer->render();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessageRegExp /^Template does not exist on path: (.+?)nonsense$/
     */
    public function testThrowsExceptionOnRenderWhenTemplatePathDoesNotExist()
    {
        $viewRenderer = new ViewRenderer("nonsense");
        $viewRenderer->setBaseDirectory(__DIR__);
        $viewRenderer->render();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessageRegExp /^Template file location is invalid; not a file: (.+?)HTML$/
     */
    public function testThrowsExceptionOnRenderWhenTemplatePathIsNotAFile()
    {
        $viewRenderer = new ViewRenderer("HTML");
        $viewRenderer->setBaseDirectory(realpath(__DIR__ . "/.."));
        $viewRenderer->render();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Parent traversal ("." or "..") is not allowed. Template path: ../HTML
     */
    public function testThrowsExceptionOnRenderWhenTemplatePathIsTraversingParents()
    {
        new ViewRenderer("../HTML");
    }

    public function testCanBeInstantiatedWithAdditionalVarsAsArray()
    {
        $viewRenderer = new ViewRenderer(basename(__FILE__), [
            "foo" => "bar"
        ]);
        $viewRenderer->setBaseDirectory(__DIR__);
        $this->assertSame("bar", $this->normalizeEOL($viewRenderer->foo));
    }

    public function testCanBeInstantiatedWithAdditionalVarsAsADifferentViewRenderer()
    {
        $viewRendererA = new ViewRenderer(basename(__FILE__), [
            "foo" => "bar"
        ]);
        $viewRendererA->setBaseDirectory(__DIR__);
        $viewRendererB = new ViewRenderer(basename(__FILE__), $viewRendererA);
        $viewRendererB->setBaseDirectory(__DIR__);
        $this->assertSame("bar", $this->normalizeEOL($viewRendererB->foo));
    }

    public function testCanRenderWhenTemplateFileExists()
    {
        $viewRenderer = new ViewRenderer("tests/unit/Kafoso/Tools/Generic/HTML/ViewRendererTest/testCanRenderWhenTemplateFileExists.phtml");
        $viewRenderer->setBaseDirectory(TESTS_RESOURCES_DIRECTORY);
        $this->assertSame("bar", $this->normalizeEOL(trim($viewRenderer->render())));
    }

    public function testCanRenderOtherViewFilesInternally()
    {
        $viewRenderer = new ViewRenderer("tests/unit/Kafoso/Tools/Generic/HTML/ViewRendererTest/testCanRenderOtherViewFilesInternally.phtml");
        $viewRenderer->setBaseDirectory(TESTS_RESOURCES_DIRECTORY);
        $this->assertSame("parent\r\nchild", $this->normalizeEOL(trim($viewRenderer->render())));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessageRegExp /^Endless loop prevented. The same template exists two or more times: (.+)testRenderThrowsExceptionWhenTryingToRenderItself\.phtml$/
     */
    public function testRenderThrowsExceptionWhenTryingToRenderItself()
    {
        $viewRenderer = new ViewRenderer("tests/unit/Kafoso/Tools/Generic/HTML/ViewRendererTest/testRenderThrowsExceptionWhenTryingToRenderItself.phtml");
        $viewRenderer->setBaseDirectory(TESTS_RESOURCES_DIRECTORY);
        $viewRenderer->setIsPrintingOutputBufferOnShutdown(false);
        $viewRenderer->render();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessageRegExp /^Endless loop prevented. The same template exists two or more times: (.+)testRenderThrowsExceptionWhenTryingToRenderAPreviouslyRenderedParent\.phtml$/
     */
    public function testRenderThrowsExceptionWhenTryingToRenderAPreviouslyRenderedParent()
    {
        $viewRenderer = new ViewRenderer("tests/unit/Kafoso/Tools/Generic/HTML/ViewRendererTest/testRenderThrowsExceptionWhenTryingToRenderAPreviouslyRenderedParent.phtml");
        $viewRenderer->setBaseDirectory(TESTS_RESOURCES_DIRECTORY);
        $viewRenderer->setIsPrintingOutputBufferOnShutdown(false);
        $viewRenderer->render();
    }

    public function testViewClassVariablesAreAvailableInViewFile()
    {
        $viewRenderer = new ViewRenderer("tests/unit/Kafoso/Tools/Generic/HTML/ViewRendererTest/testViewClassVariablesAreAvailableInViewFile.phtml");
        $viewRenderer->setBaseDirectory(TESTS_RESOURCES_DIRECTORY);
        $viewRenderer->foo = "bar";
        $this->assertSame("What: bar", $this->normalizeEOL(trim($viewRenderer->render())));
    }

    public function testInternallyRenderedViewsCanReceiveAdditionalVars()
    {
        $viewRenderer = new ViewRenderer("tests/unit/Kafoso/Tools/Generic/HTML/ViewRendererTest/testInternallyRenderedViewsCanReceiveAdditionalVars.phtml");
        $viewRenderer->setBaseDirectory(TESTS_RESOURCES_DIRECTORY);
        $viewRenderer->number = 1;
        $this->assertSame("Number: 3", $this->normalizeEOL(trim($viewRenderer->render())));
    }

    public function testInternallyRenderedViewsCanPassOnItselfToUseAsAdditionalVars()
    {
        $viewRenderer = new ViewRenderer("tests/unit/Kafoso/Tools/Generic/HTML/ViewRendererTest/testInternallyRenderedViewsCanPassOnItselfToUseAsAdditionalVars.phtml");
        $viewRenderer->setBaseDirectory(TESTS_RESOURCES_DIRECTORY);
        $viewRenderer->foo = "bar";
        $this->assertSame("bar", $this->normalizeEOL(trim($viewRenderer->render())));
    }
}
